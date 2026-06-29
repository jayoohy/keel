<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Default categories every user gets, per the PRD's transaction
     * categorization requirement.
     */
    private const DEFAULT_CATEGORIES = [
        'Food',
        'Transport',
        'Utilities',
        'Housing',
        'Shopping',
        'Entertainment',
        'Healthcare',
        'Education',
        'Investment',
        'Savings',
    ];

    public function run(): void
    {
        foreach (self::DEFAULT_CATEGORIES as $name) {
            Category::firstOrCreate(
                ['user_id' => null, 'name' => $name],
                ['is_default' => true]
            );
        }
    }
}
