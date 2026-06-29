<?php

namespace App\Services\Transactions;

use App\Models\Category;
use App\Models\CategoryOverride;
use App\Models\Transaction;

class TransactionCategorizer
{
    /**
     * Default keyword map used when a transaction doesn't match any of the
     * user's own learned overrides. Names match the PRD's default category
     * list and the rows seeded by CategorySeeder.
     */
    private const KEYWORD_MAP = [
        'Food' => ['restaurant', 'eatery', 'kitchen', 'food', 'bukka', 'bukkateria'],
        'Transport' => ['uber', 'bolt', 'taxi', 'fuel', 'petrol', 'transport', 'fare'],
        'Utilities' => ['electricity', 'nepa', 'phcn', 'water bill', 'dstv', 'gotv', 'internet', 'data bundle', 'airtime'],
        'Housing' => ['rent', 'landlord', 'mortgage'],
        'Shopping' => ['jumia', 'shoprite', 'mall', 'konga', 'amazon', 'market'],
        'Entertainment' => ['netflix', 'spotify', 'showmax', 'cinema', 'event'],
        'Healthcare' => ['hospital', 'pharmacy', 'clinic', 'health', 'medical'],
        'Education' => ['school', 'tuition', 'fees', 'udemy', 'coursera'],
        'Investment' => ['piggyvest', 'cowrywise', 'stocks', 'bonds', 'treasury bill'],
        'Savings' => ['savings', 'save'],
    ];

    /**
     * Suggest a category for a transaction, preferring the user's own
     * learned overrides over the default keyword map.
     */
    public function categorize(Transaction $transaction): ?Category
    {
        $haystack = strtolower(trim($transaction->description.' '.$transaction->narration));

        if ($haystack === '') {
            return null;
        }

        $override = CategoryOverride::where('user_id', $transaction->user_id)
            ->get()
            ->first(fn (CategoryOverride $override) => str_contains($haystack, strtolower($override->match_pattern)));

        if ($override) {
            return $override->category;
        }

        foreach (self::KEYWORD_MAP as $categoryName => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($haystack, $keyword)) {
                    return Category::where('name', $categoryName)->where('is_default', true)->first();
                }
            }
        }

        return null;
    }

    /**
     * Record that the given transaction's description should map to this
     * category going forward, so future imports reuse the user's correction.
     */
    public function remember(Transaction $transaction, Category $category): CategoryOverride
    {
        $pattern = strtolower(trim($transaction->description ?: $transaction->narration));

        return CategoryOverride::updateOrCreate(
            ['user_id' => $transaction->user_id, 'match_pattern' => $pattern],
            ['category_id' => $category->id]
        );
    }
}
