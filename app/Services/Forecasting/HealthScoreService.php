<?php

namespace App\Services\Forecasting;

use App\Models\FinancialHealthScore;
use App\Models\User;

class HealthScoreService
{
    private const INCOME_TYPES = ['credit', 'salary', 'refund'];

    private const EXPENSE_TYPES = ['debit', 'fee', 'transfer'];

    private const TRAILING_MONTHS = 3;

    public function compute(User $user): FinancialHealthScore
    {
        $savingsRate = $this->savingsRate($user);
        $emergencyFundCoverage = $this->emergencyFundCoverage($user);
        $goalProgress = $this->averageGoalProgress($user);
        $spendingStability = $this->stability($this->monthlyTotals($user, self::EXPENSE_TYPES));
        $incomeConsistency = $this->stability($this->monthlyTotals($user, self::INCOME_TYPES));

        $score = (int) round(
            ($savingsRate * 0.25)
            + ($emergencyFundCoverage * 0.25)
            + ($goalProgress * 0.20)
            + ($spendingStability * 0.15)
            + ($incomeConsistency * 0.15)
        );

        return FinancialHealthScore::create([
            'user_id' => $user->id,
            'score' => min(max($score, 0), 100),
            'savings_rate' => $savingsRate,
            'emergency_fund_coverage' => $emergencyFundCoverage,
            'spending_stability' => $spendingStability,
            'income_consistency' => $incomeConsistency,
            'computed_at' => now(),
        ]);
    }

    /**
     * This month's (income - expenses) / income, as a 0–100 score.
     */
    private function savingsRate(User $user): float
    {
        $start = now()->startOfMonth();

        $income = (float) $user->transactions()->whereIn('type', self::INCOME_TYPES)->where('transacted_at', '>=', $start)->sum('amount');
        $expenses = (float) $user->transactions()->whereIn('type', self::EXPENSE_TYPES)->where('transacted_at', '>=', $start)->sum('amount');

        if ($income <= 0) {
            return 0.0;
        }

        return round(min(max(($income - $expenses) / $income, 0), 1) * 100, 2);
    }

    /**
     * Months of average expenses the current balance would cover, capped at
     * 6 months = 100.
     */
    private function emergencyFundCoverage(User $user): float
    {
        $balance = (float) $user->accounts()->sum('balance');
        $avgMonthlyExpenses = $this->average($this->monthlyTotals($user, self::EXPENSE_TYPES));

        if ($avgMonthlyExpenses <= 0) {
            return 100.0;
        }

        return round(min($balance / $avgMonthlyExpenses / 6, 1) * 100, 2);
    }

    /**
     * Average progress percentage across active goals. Neutral (50) when
     * the user has no active goals yet, rather than penalizing them.
     */
    private function averageGoalProgress(User $user): float
    {
        $goals = $user->goals()->where('status', 'active')->get();

        if ($goals->isEmpty()) {
            return 50.0;
        }

        return round($goals->avg(fn ($goal) => $goal->progressPercentage()), 2);
    }

    /**
     * @return float[] monthly totals for the given transaction types, oldest first
     */
    private function monthlyTotals(User $user, array $types): array
    {
        $totals = [];

        for ($i = self::TRAILING_MONTHS - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            $totals[] = (float) $user->transactions()
                ->whereIn('type', $types)
                ->whereBetween('transacted_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('amount');
        }

        return $totals;
    }

    private function average(array $values): float
    {
        return count($values) > 0 ? array_sum($values) / count($values) : 0.0;
    }

    /**
     * Converts the coefficient of variation across monthly totals into a
     * 0–100 stability score (lower variation = higher score). Neutral (50)
     * when there isn't enough data yet to judge variation.
     */
    private function stability(array $monthlyTotals): float
    {
        $mean = $this->average($monthlyTotals);

        if ($mean <= 0 || count($monthlyTotals) < 2) {
            return 50.0;
        }

        $variance = array_sum(array_map(fn ($value) => ($value - $mean) ** 2, $monthlyTotals)) / count($monthlyTotals);
        $coefficientOfVariation = sqrt($variance) / $mean;

        return round(min(max(1 - $coefficientOfVariation, 0), 1) * 100, 2);
    }
}
