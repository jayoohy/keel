<?php

namespace App\Services\Rules;

use App\Exceptions\InsufficientBalanceException;
use App\Models\Rule;
use App\Models\RuleExecution;
use App\Models\Transaction;
use App\Notifications\RuleExecuted;
use App\Services\Allocation\AllocationEngine;

class RuleEvaluator
{
    public function __construct(private AllocationEngine $allocationEngine) {}

    /**
     * Match the transaction against the user's active rules and apply any
     * that fire. Called during the scheduled sync, right after a
     * transaction is imported and categorized.
     */
    public function evaluate(Transaction $transaction): void
    {
        $rules = Rule::where('user_id', $transaction->user_id)->where('is_active', true)->get();

        foreach ($rules as $rule) {
            if ($this->matches($rule, $transaction)) {
                $this->execute($rule, $transaction);
            }
        }
    }

    private function matches(Rule $rule, Transaction $transaction): bool
    {
        $value = strtolower($rule->trigger_value);

        return match ($rule->trigger_type) {
            'transaction_type' => strtolower($transaction->type) === $value,
            'category' => $transaction->category && strtolower($transaction->category->name) === $value,
            'merchant' => str_contains(strtolower($transaction->description.' '.$transaction->narration), $value),
            default => false,
        };
    }

    private function execute(Rule $rule, Transaction $transaction): void
    {
        $amount = $rule->action_basis === 'percentage'
            ? round((float) $transaction->amount * ((float) $rule->action_value / 100), 2)
            : (float) $rule->action_value;

        if ($amount <= 0) {
            return;
        }

        try {
            $allocation = $this->allocationEngine->allocate(
                $transaction->user,
                $rule->goal,
                $amount,
                type: 'automatic',
                source: 'rule',
                transaction: $transaction,
            );
        } catch (InsufficientBalanceException) {
            // Not enough unallocated balance to honor the rule — skip rather
            // than partially allocate. Nothing is logged since the rule
            // didn't actually fire.
            return;
        }

        RuleExecution::create([
            'rule_id' => $rule->id,
            'transaction_id' => $transaction->id,
            'allocation_id' => $allocation->id,
            'executed_at' => now(),
        ]);

        $transaction->user->notify(new RuleExecuted($rule, $allocation));
    }
}
