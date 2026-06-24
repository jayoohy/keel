import {
  ConflictException,
  Injectable,
  UnauthorizedException,
} from '@nestjs/common';
import { ConfigService } from '@nestjs/config';
import { JwtService } from '@nestjs/jwt';
import * as bcrypt from 'bcryptjs';
import { randomUUID } from 'crypto';
import { UsersService } from '../users/users.service';
import { User } from '../database/schema';
import { RefreshTokensRepository } from './refresh-tokens.repository';
import { RegisterDto } from './dto/register.dto';
import { RefreshTokenDto } from './dto/refresh-token.dto';

@Injectable()
export class AuthService {
  constructor(
    private readonly usersService: UsersService,
    private readonly refreshTokensRepo: RefreshTokensRepository,
    private readonly jwtService: JwtService,
    private readonly config: ConfigService,
  ) {}

  async validateUser(email: string, password: string): Promise<User | null> {
    const user = await this.usersService.findByEmail(email);
    if (!user) return null;
    const valid = await bcrypt.compare(password, user.password);
    return valid ? user : null;
  }

  async register(dto: RegisterDto) {
    const existing = await this.usersService.findByEmail(dto.email);
    if (existing) throw new ConflictException('Email already in use');
    const hashed = await bcrypt.hash(dto.password, 12);
    const user = await this.usersService.create({
      name: dto.name,
      email: dto.email,
      password: hashed,
    });
    return this.issueTokens(user);
  }

  async login(user: User) {
    return this.issueTokens(user);
  }

  async refresh(dto: RefreshTokenDto) {
    let payload: { sub: string; email: string; jti: string };
    try {
      payload = this.jwtService.verify(dto.refreshToken, {
        secret: this.config.getOrThrow<string>('JWT_REFRESH_SECRET'),
      });
    } catch {
      throw new UnauthorizedException('Invalid or expired refresh token');
    }

    const record = await this.refreshTokensRepo.findByJti(
      payload.jti,
      payload.sub,
    );
    if (!record) throw new UnauthorizedException('Refresh token revoked');

    const user = await this.usersService.findById(payload.sub);
    if (!user) throw new UnauthorizedException('User not found');

    await this.refreshTokensRepo.deleteById(record.id);
    return this.issueTokens(user);
  }

  async logout(refreshToken: string) {
    try {
      const payload = this.jwtService.verify<{ jti: string }>(refreshToken, {
        secret: this.config.getOrThrow<string>('JWT_REFRESH_SECRET'),
      });
      await this.refreshTokensRepo.deleteByJti(payload.jti);
    } catch {
      // Expired or invalid — nothing to clean up
    }
  }

  async logoutAll(userId: string) {
    await this.refreshTokensRepo.deleteAllForUser(userId);
  }

  private async issueTokens(user: User) {
    const jti = randomUUID();
    const payload = { sub: user.id, email: user.email };

    const accessSecret = this.config.getOrThrow<string>('JWT_SECRET');
    const refreshSecret = this.config.getOrThrow<string>('JWT_REFRESH_SECRET');

    // expiresIn must be a string literal to satisfy ms.StringValue — read from env then cast
    const accessExpiry = (this.config.get('JWT_EXPIRES_IN') ?? '15m') as never;
    const refreshExpiry = (this.config.get('JWT_REFRESH_EXPIRES_IN') ?? '30d') as never;

    const accessToken = this.jwtService.sign(payload, {
      secret: accessSecret,
      expiresIn: accessExpiry,
    });

    const refreshToken = this.jwtService.sign(
      { ...payload, jti },
      {
        secret: refreshSecret,
        expiresIn: refreshExpiry,
      },
    );

    const expiresAt = new Date();
    expiresAt.setDate(expiresAt.getDate() + 30);
    await this.refreshTokensRepo.create({ userId: user.id, jti, expiresAt });

    const { password: _pw, ...safeUser } = user;
    return { user: safeUser, accessToken, refreshToken };
  }
}
