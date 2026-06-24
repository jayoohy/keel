import { Injectable } from '@nestjs/common';
import { UsersRepository } from './users.repository';
import { NewUser } from '../database/schema';

@Injectable()
export class UsersService {
  constructor(private readonly repo: UsersRepository) {}

  findById(id: string) {
    return this.repo.findById(id);
  }

  findByEmail(email: string) {
    return this.repo.findByEmail(email);
  }

  create(data: NewUser) {
    return this.repo.create(data);
  }

  update(id: string, data: Partial<NewUser>) {
    return this.repo.update(id, data);
  }

  delete(id: string) {
    return this.repo.delete(id);
  }
}
