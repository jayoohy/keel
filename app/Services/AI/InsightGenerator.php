<?php

namespace App\Services\AI;

use App\Models\Goal;
use App\Models\Insight;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Computes the underlying numbers deterministically in PHP, then uses
 * OpenAI only to phrase the plain-language sentence — keeps the figures
 * accurate/testable while still using AI for what it's good at.
 */
class InsightGenerator
{
    private const SIGNIFICANT_CHANGE_PERCENT = 15.0;

    public function __construct(private OpenAiClient $client) {}

    public function generateSpendingInsight(User $user): ?Insight
    {
        $alreadyGenerated = Insight::where('user_id', $user->id)
            ->where('type', 'spending')
            ->where('created_at', '>=', now()->startOfMonth())
            ->exists();

        if ($alreadyGenerated) {
            return null;
        }

        $current = $this->categorySpend($user, now());
        $previous = $this->categorySpend($user, now()->subMonth());

        $biggestCategory = null;
        $biggestPercentChange = 0.0;

        foreach ($current as $category => $amount) {
            $previousAmount = $previous[$category] ?? 0.0;

            if ($previousAmount <= 0) {
                continue;
            }

            $percentChange = (($amount - $previousAmount) / $previousAmount) * 100;

            if (abs($percentChange) > abs($biggestPercentChange)) {
                $biggestPercentChange = $percentChange;
                $biggestCategory = $category;
            }
        }

        if (! $biggestCategory || abs($biggestPercentChange) < self::SIGNIFICANT_CHANGE_PERCENT) {
            return null;
        }

        $direction = $biggestPercentChange > 0 ? 'more' : 'less';
        $percent = round(abs($biggestPercentChange));

        $message = $this->client->chat([
            ['role' => 'system', 'content' => 'Write one short, plain-language sentence summarizing a spending change. Be concise and factual — do not invent numbers beyond what is given.'],
            ['role' => 'user', 'content' => "The user spent {$percent}% {$direction} on {$biggestCategory} this month compared to last month."],
        ]);

        return Insight::create([
            'user_id' => $user->id,
            'type' => 'spending',
            'title' => 'Spending update',
            'message' => $message,
        ]);
    }

    public function generateGoalProgressInsight(Goal $goal): ?Insight
    {
        $alreadyGenerated = Insight::where('user_id', $goal->user_id)
            ->where('type', 'goal_progress')
            ->where('related_type', Goal::class)
            ->where('related_id', $goal->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->exists();

        if ($alreadyGenerated) {
            return null;
        }

        $forecast = $goal->forecast;

        if (! $forecast || ! $forecast->projected_completion_date) {
            return null;
        }

        $message = $this->client->chat([
            ['role' => 'system', 'content' => 'Write one short, encouraging, plain-language sentence about progress toward a savings goal. Be concise and factual — do not invent numbers beyond what is given.'],
            ['role' => 'user', 'content' => "At the current savings rate, the goal \"{$goal->name}\" is projected to be reached around {$forecast->projected_completion_date->format('F Y')}."],
        ]);

        return Insight::create([
            'user_id' => $goal->user_id,
            'type' => 'goal_progress',
            'title' => "Goal progress: {$goal->name}",
            'message' => $message,
            'related_type' => Goal::class,
            'related_id' => $goal->id,
        ]);
    }

    public function generateAnomalyInsight(Transaction $transaction): ?Insight
    {
        $alreadyFlagged = Insight::where('type', 'anomaly')
            ->where('related_type', Transaction::class)
            ->where('related_id', $transaction->id)
            ->exists();

        if ($alreadyFlagged) {
            return null;
        }

        return Insight::create([
            'user_id' => $transaction->user_id,
            'type' => 'anomaly',
            'title' => 'Unusual transaction detected',
            'message' => sprintf(
                '%s of %s on %s is significantly higher than your recent average — worth a second look.',
                ucfirst($transaction->type),
                number_format((float) $transaction->amount, 2),
                $transaction->transacted_at->format('M j, Y'),
            ),
            'related_type' => Transaction::class,
            'related_id' => $transaction->id,
        ]);
    }

    /**
     * @return array<string, float>
     */
    private function categorySpend(User $user, Carbon $month): array
    {
        return $user->transactions()
            ->whereIn('type', ['debit', 'fee'])
            ->whereBetween('transacted_at', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ])
            ->with('category')
            ->get()
            ->groupBy(fn (Transaction $t) => $t->category?->name ?? 'Uncategorized')
            ->map(fn ($group) => (float) $group->sum('amount'))
            ->all();
    }
}
