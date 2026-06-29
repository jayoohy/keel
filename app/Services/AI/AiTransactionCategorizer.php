<?php

namespace App\Services\AI;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Fallback categorizer for transactions the rules-based
 * TransactionCategorizer (see app/Services/Transactions) couldn't match.
 * Only ever called from the scheduled batch job (see PRD §7/§9.5).
 */
class AiTransactionCategorizer
{
    public function __construct(private OpenAiClient $client) {}

    public function categorizeUncategorized(User $user, int $limit = 20): int
    {
        $categories = Category::where('is_default', true)
            ->orWhere('user_id', $user->id)
            ->pluck('name');

        if ($categories->isEmpty()) {
            return 0;
        }

        $transactions = $user->transactions()->whereNull('category_id')->limit($limit)->get();
        $updated = 0;

        foreach ($transactions as $transaction) {
            $category = $this->suggestCategory($transaction, $categories);

            if ($category) {
                $transaction->update(['category_id' => $category->id]);
                $updated++;
            }
        }

        return $updated;
    }

    private function suggestCategory(Transaction $transaction, Collection $categories): ?Category
    {
        $description = trim($transaction->description.' '.$transaction->narration);

        if ($description === '') {
            return null;
        }

        $suggested = $this->client->chat([
            [
                'role' => 'system',
                'content' => 'You categorize bank transactions. Respond with exactly one category name from the provided list and nothing else.',
            ],
            [
                'role' => 'user',
                'content' => sprintf(
                    "Categories: %s\n\nTransaction description: %s\nAmount: %s %s",
                    $categories->implode(', '),
                    $description,
                    $transaction->amount,
                    $transaction->currency,
                ),
            ],
        ]);

        return Category::where('name', $suggested)
            ->where(function ($query) use ($transaction) {
                $query->where('is_default', true)->orWhere('user_id', $transaction->user_id);
            })
            ->first();
    }
}
