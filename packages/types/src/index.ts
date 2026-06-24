// ── Auth ────────────────────────────────────────────────────────────────────
export interface AuthUser {
  id: string;
  name: string;
  email: string;
  currency: string;
  emailVerifiedAt: string | null;
}

export interface LoginDto {
  email: string;
  password: string;
}

export interface RegisterDto {
  name: string;
  email: string;
  password: string;
}

export interface AuthResponse {
  user: AuthUser;
  accessToken: string;
  refreshToken: string;
}

// ── API ─────────────────────────────────────────────────────────────────────
export interface ApiResponse<T> {
  data: T;
  message?: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  total: number;
  page: number;
  limit: number;
}

// ── Goals ───────────────────────────────────────────────────────────────────
export type GoalStatus = 'active' | 'paused' | 'completed' | 'cancelled';
export type GoalType =
  | 'house'
  | 'wedding'
  | 'relocation'
  | 'emergency'
  | 'car'
  | 'business'
  | 'vacation'
  | 'education'
  | 'custom';

export interface Goal {
  id: string;
  name: string;
  description: string | null;
  type: GoalType;
  emoji: string;
  targetAmount: number;
  currentAmount: number;
  deadline: string | null;
  priority: number;
  status: GoalStatus;
  createdAt: string;
}

// ── Transactions ─────────────────────────────────────────────────────────────
export type TransactionType = 'debit' | 'credit' | 'transfer' | 'fee' | 'salary' | 'refund';

export interface Transaction {
  id: string;
  accountId: string;
  description: string;
  amount: number;
  type: TransactionType;
  categoryId: string | null;
  date: string;
  isSalary: boolean;
  isRecurring: boolean;
  createdAt: string;
}

// ── Accounts ─────────────────────────────────────────────────────────────────
export interface BankAccount {
  id: string;
  name: string;
  accountNumber: string;
  institutionName: string;
  institutionLogo: string | null;
  balance: number;
  currency: string;
  isPrimary: boolean;
}

// ── Dashboard ─────────────────────────────────────────────────────────────────
export interface DashboardSummary {
  totalBalance: number;
  monthlyIncome: number;
  monthlyExpenses: number;
  monthlySavings: number;
  savingsRate: number;
  totalAccounts: number;
}
