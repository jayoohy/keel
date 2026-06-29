<?php

namespace App\Services\Allocation;

use App\Exceptions\InsufficientBalanceException;
use App\Models\Allocation;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\GoalCompleted;
use App\Notifications\GoalUpdated;

class AllocationEngine
{
    /**
     * The sum of balances across all of the user's connected accounts —
     * the real money that allocations are virtually carved out of.
     */
    public function availableBalance(User $user): float
    {
        return (float) $user->accounts()->sum('balance');
    }

    public function totalAllocated(User $user): float
    {
        return (float) Allocation::where('user_id', $user->id)->sum('amount');
    }

    public function unallocatedBalance(User $user): float
    {
        return round($this->availableBalance($user) - $this->totalAllocated($user), 2);
    }

    /**
     * @throws InsufficientBalanceException
     */
    public function allocate(
        User $user,
        Goal $goal,
        float $amount,
        string $type = 'manual',
        string $source = 'user',
        ?Transaction $transaction = null,
    ): Allocation {
        $unallocated = $this->unallocatedBalance($user);

        if ($amount > $unallocated + 0.001) {
            throw new InsufficientBalanceException($amount, $unallocated);
        }

        $allocation = Allocation::create([
            'user_id' => $user->id,
            'goal_id' => $goal->id,
            'transaction_id' => $transaction?->id,
            'amount' => $amount,
            'type' => $type,
            'source' => $source,
        ]);

        $goal->increment('current_amount', $amount);
        $goal->refresh();

        // Rule-driven allocations get their own RuleExecuted notification
        // (see RuleEvaluator) — avoid notifying twice for the same event.
        if ($source === 'user') {
            $user->notify(new GoalUpdated($goal, $allocation));
        }

        if ($goal->status === 'active' && (float) $goal->current_amount >= (float) $goal->target_amount) {
            $goal->update(['status' => 'completed']);
            $user->notify(new GoalCompleted($goal));
        }

        return $allocation;
    }

    public function deallocate(Allocation $allocation): void
    {
        $allocation->goal->decrement('current_amount', $allocation->amount);
        $allocation->delete();
    }
}
