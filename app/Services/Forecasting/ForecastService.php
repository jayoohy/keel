<?php

namespace App\Services\Forecasting;

use App\Models\Goal;
use App\Models\GoalForecast;
use App\Models\User;

class ForecastService
{
    private const INCOME_TYPES = ['credit', 'salary', 'refund'];

    private const EXPENSE_TYPES = ['debit', 'fee', 'transfer'];

    /**
     * Average monthly net savings (income - expenses) over the trailing
     * N months, used to project both goal completion and near-term balance.
     */
    public function averageMonthlySaving(User $user, int $months = 3): float
    {
        $start = now()->subMonths($months)->startOfMonth();

        $income = (float) $user->transactions()
            ->whereIn('type', self::INCOME_TYPES)
            ->where('transacted_at', '>=', $start)
            ->sum('amount');

        $expenses = (float) $user->transactions()
            ->whereIn('type', self::EXPENSE_TYPES)
            ->where('transacted_at', '>=', $start)
            ->sum('amount');

        return round(($income - $expenses) / $months, 2);
    }

    public function forecastGoal(Goal $goal): GoalForecast
    {
        $averageSaving = $this->averageMonthlySaving($goal->user);
        $remaining = max((float) $goal->target_amount - (float) $goal->current_amount, 0);

        $projectedDate = $averageSaving > 0 && $remaining > 0
            ? now()->addMonths((int) ceil($remaining / $averageSaving))->startOfDay()
            : ($remaining <= 0 ? now()->startOfDay() : null);

        return GoalForecast::create([
            'goal_id' => $goal->id,
            'projected_completion_date' => $projectedDate,
            'average_monthly_saving' => $averageSaving,
            'computed_at' => now(),
        ]);
    }

    /**
     * Projected total account balance N months from now, assuming the
     * trailing average savings rate holds.
     */
    public function forecastBalance(User $user, int $monthsAhead = 1): float
    {
        $averageSaving = $this->averageMonthlySaving($user);
        $currentBalance = (float) $user->accounts()->sum('balance');

        return round($currentBalance + ($averageSaving * $monthsAhead), 2);
    }
}
