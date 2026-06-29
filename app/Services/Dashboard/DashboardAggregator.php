<?php

namespace App\Services\Dashboard;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardAggregator
{
    private const INCOME_TYPES = ['credit', 'salary', 'refund'];

    private const EXPENSE_TYPES = ['debit', 'fee', 'transfer'];

    /**
     * Cached briefly so the dashboard's polling refetch (see PRD §7 — no
     * WebSockets on this hosting tier) doesn't recompute on every request.
     */
    public function summary(User $user): array
    {
        return Cache::remember("dashboard.summary.{$user->id}", 60, function () use ($user) {
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();

            $income = (float) $user->transactions()
                ->whereIn('type', self::INCOME_TYPES)
                ->whereBetween('transacted_at', [$start, $end])
                ->sum('amount');

            $expenses = (float) $user->transactions()
                ->whereIn('type', self::EXPENSE_TYPES)
                ->whereBetween('transacted_at', [$start, $end])
                ->sum('amount');

            $savings = $income - $expenses;

            return [
                'totalBalance' => (float) $user->accounts()->sum('balance'),
                'monthlyIncome' => $income,
                'monthlyExpenses' => $expenses,
                'monthlySavings' => $savings,
                'savingsRate' => $income > 0 ? round(($savings / $income) * 100, 2) : 0.0,
                'netCashFlow' => $savings,
            ];
        });
    }

    public function spendingByCategory(User $user): array
    {
        return Cache::remember("dashboard.spending-by-category.{$user->id}", 60, function () use ($user) {
            return $user->transactions()
                ->whereIn('type', self::EXPENSE_TYPES)
                ->whereBetween('transacted_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->with('category')
                ->get()
                ->groupBy(fn ($transaction) => $transaction->category?->name ?? 'Uncategorized')
                ->map(fn ($group, $name) => [
                    'category' => $name,
                    'total' => (float) $group->sum('amount'),
                ])
                ->values()
                ->all();
        });
    }

    public function incomeExpenseTrend(User $user, int $months = 6): array
    {
        return Cache::remember("dashboard.trend.{$user->id}.{$months}", 60, function () use ($user, $months) {
            $trend = [];

            for ($i = $months - 1; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $start = $month->copy()->startOfMonth();
                $end = $month->copy()->endOfMonth();

                $trend[] = [
                    'month' => $month->format('M Y'),
                    'income' => (float) $user->transactions()
                        ->whereIn('type', self::INCOME_TYPES)
                        ->whereBetween('transacted_at', [$start, $end])
                        ->sum('amount'),
                    'expenses' => (float) $user->transactions()
                        ->whereIn('type', self::EXPENSE_TYPES)
                        ->whereBetween('transacted_at', [$start, $end])
                        ->sum('amount'),
                ];
            }

            return $trend;
        });
    }
}
