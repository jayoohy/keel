import { Inject, Injectable } from '@nestjs/common';
import { and, eq, gt } from 'drizzle-orm';
import { NodePgDatabase } from 'drizzle-orm/node-postgres';
import { DRIZZLE } from '../database/database.module';
import * as schema from '../database/schema';

@Injectable()
export class RefreshTokensRepository {
  constructor(
    @Inject(DRIZZLE) private db: NodePgDatabase<typeof schema>,
  ) {}

  async create(data: { userId: string; jti: string; expiresAt: Date }) {
    const [token] = await this.db
      .insert(schema.refreshTokens)
      .values(data)
      .returning();
    return token;
  }

  async findByJti(jti: string, userId: string) {
    const [token] = await this.db
      .select()
      .from(schema.refreshTokens)
      .where(
        and(
          eq(schema.refreshTokens.jti, jti),
          eq(schema.refreshTokens.userId, userId),
          gt(schema.refreshTokens.expiresAt, new Date()),
        ),
      );
    return token ?? null;
  }

  async deleteById(id: string) {
    await this.db
      .delete(schema.refreshTokens)
      .where(eq(schema.refreshTokens.id, id));
  }

  async deleteByJti(jti: string) {
    await this.db
      .delete(schema.refreshTokens)
      .where(eq(schema.refreshTokens.jti, jti));
  }

  async deleteAllForUser(userId: string) {
    await this.db
      .delete(schema.refreshTokens)
      .where(eq(schema.refreshTokens.userId, userId));
  }
}
