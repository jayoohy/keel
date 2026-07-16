<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Allocation;
use App\Models\BankConnection;
use App\Models\Category;
use App\Models\FinancialHealthScore;
use App\Models\Goal;
use App\Models\GoalForecast;
use App\Models\Insight;
use App\Models\Rule;
use App\Models\RuleExecution;
use App\Models\SyncLog;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\GoalCompleted;
use App\Notifications\GoalUpdated;
use App\Notifications\LargeSpendingAlert;
use App\Notifications\RuleExecuted;
use App\Notifications\SyncErrorAlert;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Seeds a full, coherent demo dataset (6 months of activity) for one user
 * so every screen of the UI has something realistic to show.
 *
 * Usage: php artisan db:seed --class=DemoDataSeeder
 * Re-running wipes and rebuilds the demo data for the same user.
 */
class DemoDataSeeder extends Seeder
{
    private const DEMO_EMAIL = 'ogukahjoy@gmail.com';

    public function run(): void
    {
        $user = User::where('email', self::DEMO_EMAIL)->first() ?? User::firstOrFail();

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $this->call(CategorySeeder::class);
        $this->wipeExistingData($user);

        $categories = Category::whereNull('user_id')->pluck('id', 'name');

        $connection = BankConnection::factory()->for($user)->create([
            'institution_name' => 'GTBank',
            'connected_at' => now()->subMonths(6),
        ]);

        $current = Account::factory()->for($user)->for($connection)->create([
            'name' => 'Current Account',
            'account_type' => 'CURRENT_ACCOUNT',
            'balance' => 0,
        ]);
        $savings = Account::factory()->for($user)->for($connection)->create([
            'name' => 'Savings Account',
            'account_type' => 'SAVINGS_ACCOUNT',
            'balance' => 0,
        ]);

        $salaries = $this->seedTransactions($user, $current, $savings, $categories);
        [$emergencyFund, $completedGoal] = $this->seedGoalsAndRules($user, $salaries);
        $this->seedScoresAndInsights($user, $categories);
        $this->seedSyncLogs($user, $connection);
        $this->seedNotifications($user, $emergencyFund, $completedGoal);
    }

    private function wipeExistingData(User $user): void
    {
        RuleExecution::whereIn('rule_id', $user->rules()->pluck('id'))->delete();
        GoalForecast::whereIn('goal_id', $user->goals()->pluck('id'))->delete();
        Allocation::where('user_id', $user->id)->delete();
        Rule::where('user_id', $user->id)->delete();
        Goal::where('user_id', $user->id)->delete();
        Transaction::where('user_id', $user->id)->delete();
        Account::where('user_id', $user->id)->delete();
        SyncLog::where('user_id', $user->id)->delete();
        BankConnection::where('user_id', $user->id)->delete();
        Insight::where('user_id', $user->id)->delete();
        FinancialHealthScore::where('user_id', $user->id)->delete();
        $user->notifications()->delete();
    }

    /**
     * Six months of salary, bills, and day-to-day spending on the current
     * account, plus monthly transfers into the savings account.
     *
     * @return array<int, Transaction> salary transactions, oldest first
     */
    private function seedTransactions(User $user, Account $current, Account $savings, $categories): array
    {
        $spendingPatterns = [
            // [description, narration, category, min, max, times-per-month]
            ['Chowdeck order', 'CHOWDECK*LAGOS', 'Food', 4500, 15000, 4],
            ['Shoprite Lekki', 'POS PURCHASE SHOPRITE', 'Food', 18000, 45000, 2],
            ['Bolt ride', 'BOLT.EU/O/2416', 'Transport', 2200, 8500, 5],
            ['Fuel - NNPC station', 'POS PURCHASE NNPC RETAIL', 'Transport', 15000, 30000, 2],
            ['IKEDC prepaid tokens', 'IKEDC*PREPAID', 'Utilities', 15000, 25000, 1],
            ['Spectranet renewal', 'SPECTRANET LTD', 'Utilities', 19800, 19800, 1],
            ['MTN airtime', 'MTN VTU RECHARGE', 'Utilities', 2000, 5000, 3],
            ['Jumia order', 'JUMIA PAY NG', 'Shopping', 9000, 60000, 1],
            ['Netflix subscription', 'NETFLIX.COM', 'Entertainment', 7000, 7000, 1],
            ['Filmhouse cinema', 'POS PURCHASE FILMHOUSE', 'Entertainment', 8000, 16000, 1],
            ['Medplus pharmacy', 'POS PURCHASE MEDPLUS', 'Healthcare', 5000, 22000, 1],
        ];

        $specs = [];
        $windowStart = now()->copy()->startOfMonth()->subMonths(5);

        for ($m = 0; $m < 6; $m++) {
            $month = $windowStart->copy()->addMonths($m);

            $this->pushSpec($specs, $month->copy()->day(25)->setTime(9, 14), 'salary', 850000,
                'STILT-TECH SALARY', 'SALARY PAYMENT STILT-TECH DIGITAL', null, $current, 'salary');

            $this->pushSpec($specs, $month->copy()->day(2)->setTime(8, 30), 'debit', 200000,
                'Rent transfer', 'NIP TRANSFER TO LANDLORD', 'Housing', $current);

            $this->pushSpec($specs, $month->copy()->day(26)->setTime(10, 0), 'transfer', 100000,
                'Transfer to savings', 'OWN ACCOUNT TRANSFER', 'Savings', $current);
            $this->pushSpec($specs, $month->copy()->day(26)->setTime(10, 1), 'credit', 100000,
                'Transfer from current', 'OWN ACCOUNT TRANSFER', 'Savings', $savings);

            $this->pushSpec($specs, $month->copy()->day(26)->setTime(10, 0, 5), 'fee', 53.75,
                'Transfer levy', 'EMT LEVY + NIP CHARGE', null, $current);

            foreach ($spendingPatterns as [$desc, $narration, $category, $min, $max, $perMonth]) {
                for ($i = 0; $i < $perMonth; $i++) {
                    $day = fake()->numberBetween(1, min(28, $month->isSameMonth(now()) ? now()->day : 28));
                    $this->pushSpec($specs, $month->copy()->day($day)->setTime(fake()->numberBetween(8, 21), fake()->numberBetween(0, 59)),
                        'debit', fake()->randomFloat(2, $min, $max), $desc, $narration, $category, $current);
                }
            }
        }

        // One conspicuous outlier for the anomaly insight / large-spending alert.
        $this->pushSpec($specs, now()->copy()->subDays(3)->setTime(19, 42), 'debit', 315000,
            'IKEA furniture order', 'POS PURCHASE LIFEMATE FURNITURE', 'Shopping', $current);

        // Quarterly interest on savings.
        $this->pushSpec($specs, $windowStart->copy()->addMonths(2)->endOfMonth(), 'credit', 5120.33,
            'Interest payment', 'INTEREST CAPITALISATION', null, $savings);
        $this->pushSpec($specs, $windowStart->copy()->addMonths(5)->endOfMonth(), 'credit', 7893.10,
            'Interest payment', 'INTEREST CAPITALISATION', null, $savings);

        $specs = array_filter($specs, fn ($s) => $s['at'] <= now());
        usort($specs, fn ($a, $b) => $a['at'] <=> $b['at']);

        $balances = [$current->id => 420000.00, $savings->id => 500000.00];
        $salaries = [];

        foreach ($specs as $spec) {
            $isIncome = in_array($spec['type'], ['credit', 'salary', 'refund'], true);
            $balances[$spec['account']->id] = round(
                $balances[$spec['account']->id] + ($isIncome ? $spec['amount'] : -$spec['amount']), 2
            );

            $transaction = Transaction::factory()->for($user)->for($spec['account'])->create([
                'category_id' => $spec['category'] ? $categories[$spec['category']] : null,
                'type' => $spec['type'],
                'amount' => $spec['amount'],
                'description' => $spec['description'],
                'narration' => $spec['narration'],
                'balance_after' => $balances[$spec['account']->id],
                'transacted_at' => $spec['at'],
                'created_at' => $spec['at'],
            ]);

            if ($spec['tag'] === 'salary') {
                $salaries[] = $transaction;
            }
        }

        $current->update(['balance' => $balances[$current->id]]);
        $savings->update(['balance' => $balances[$savings->id]]);

        return $salaries;
    }

    private function pushSpec(array &$specs, Carbon $at, string $type, float $amount,
        string $description, string $narration, ?string $category, Account $account, ?string $tag = null): void
    {
        $specs[] = compact('at', 'type', 'amount', 'description', 'narration', 'category', 'account', 'tag');
    }

    /**
     * @param  array<int, Transaction>  $salaries
     * @return array{0: Goal, 1: Goal} the emergency fund and the completed goal
     */
    private function seedGoalsAndRules(User $user, array $salaries): array
    {
        $emergencyFund = Goal::factory()->for($user)->create([
            'name' => 'Emergency Fund',
            'description' => 'Six months of living expenses as a safety net.',
            'target_amount' => 2000000,
            'priority' => 'high',
            'deadline' => now()->addMonths(8),
        ]);
        $relocation = Goal::factory()->for($user)->create([
            'name' => 'Relocation Fund',
            'description' => 'Moving and settling-in costs for the new apartment.',
            'target_amount' => 3500000,
            'priority' => 'medium',
            'deadline' => now()->addMonths(18),
        ]);
        $laptop = Goal::factory()->for($user)->create([
            'name' => 'New MacBook',
            'description' => 'M4 MacBook Pro for work.',
            'target_amount' => 1200000,
            'priority' => 'low',
            'deadline' => now()->addMonths(3),
        ]);
        $trip = Goal::factory()->for($user)->create([
            'name' => 'Abuja Trip',
            'description' => 'Flights and hotel for TechCabal conference.',
            'target_amount' => 450000,
            'current_amount' => 450000,
            'priority' => 'medium',
            'deadline' => now()->subWeeks(2),
            'status' => 'completed',
        ]);

        $rule = Rule::factory()->for($user)->create([
            'goal_id' => $emergencyFund->id,
            'name' => 'Save 20% of salary',
            'trigger_type' => 'transaction_type',
            'trigger_value' => 'salary',
            'action_basis' => 'percentage',
            'action_value' => 20,
        ]);
        Rule::factory()->for($user)->create([
            'goal_id' => $laptop->id,
            'name' => 'Food delivery match',
            'trigger_type' => 'merchant',
            'trigger_value' => 'chowdeck',
            'action_basis' => 'fixed',
            'action_value' => 2000,
        ]);

        // The salary rule fired on every payday: 20% of 850k into the emergency fund.
        foreach ($salaries as $salary) {
            $allocation = Allocation::factory()->for($user)->create([
                'goal_id' => $emergencyFund->id,
                'transaction_id' => $salary->id,
                'amount' => 170000,
                'type' => 'automatic',
                'source' => 'rule',
                'created_at' => $salary->transacted_at,
            ]);
            RuleExecution::create([
                'rule_id' => $rule->id,
                'transaction_id' => $salary->id,
                'allocation_id' => $allocation->id,
                'executed_at' => $salary->transacted_at,
            ]);
            $emergencyFund->increment('current_amount', 170000);
        }

        // Manual top-ups for the other goals, spread over recent months.
        foreach ([[$relocation, [400000, 350000, 300000, 250000]], [$laptop, [500000, 320000, 200000]]] as [$goal, $amounts]) {
            foreach ($amounts as $i => $amount) {
                Allocation::factory()->for($user)->create([
                    'goal_id' => $goal->id,
                    'amount' => $amount,
                    'created_at' => now()->subMonths(count($amounts) - $i)->subDays(fake()->numberBetween(0, 6)),
                ]);
                $goal->increment('current_amount', $amount);
            }
        }
        Allocation::factory()->for($user)->create([
            'goal_id' => $trip->id,
            'amount' => 450000,
            'created_at' => now()->subMonths(2),
        ]);

        foreach ([$emergencyFund, $relocation, $laptop] as $goal) {
            $monthly = (float) ($goal->id === $emergencyFund->id ? 170000 : 150000);
            $remaining = max(0, (float) $goal->target_amount - (float) $goal->current_amount);
            GoalForecast::factory()->create([
                'goal_id' => $goal->id,
                'projected_completion_date' => now()->addMonths((int) ceil($remaining / $monthly)),
                'average_monthly_saving' => $monthly,
            ]);
        }

        return [$emergencyFund, $trip];
    }

    private function seedScoresAndInsights(User $user, $categories): void
    {
        // Weekly health scores trending upward over ~2 months.
        foreach (range(7, 0) as $i => $weeksAgo) {
            FinancialHealthScore::factory()->for($user)->create([
                'score' => 52 + ($i * 3) + fake()->numberBetween(-1, 1),
                'savings_rate' => 24 + $i,
                'emergency_fund_coverage' => 40 + ($i * 4),
                'spending_stability' => fake()->randomFloat(2, 60, 75),
                'income_consistency' => fake()->randomFloat(2, 88, 96),
                'computed_at' => now()->subWeeks($weeksAgo),
            ]);
        }

        $insights = [
            ['spending', 'Food spending up 18%', 'You spent 18% more on Food this month than your three-month average. Chowdeck orders account for most of the increase.', false, 2],
            ['anomaly', 'Unusual transaction detected', 'A debit of 315,000.00 at Lifemate Furniture is significantly higher than your recent average transaction.', false, 3],
            ['goal_progress', 'Emergency Fund past the halfway mark', 'You have saved more than 50% of your Emergency Fund target. At your current pace you will finish ahead of your deadline.', true, 6],
            ['categorization', '12 transactions categorized', 'We automatically categorized 12 new transactions from your last sync. Review them to keep your reports accurate.', true, 8],
            ['spending', 'Transport costs steady', 'Your Transport spending has stayed within 5% of its average for three consecutive months. Consistent costs make forecasting more reliable.', false, 12],
        ];
        foreach ($insights as [$type, $title, $message, $isRead, $daysAgo]) {
            Insight::factory()->for($user)->create([
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'is_read' => $isRead,
                'created_at' => now()->subDays($daysAgo),
            ]);
        }
    }

    private function seedSyncLogs(User $user, BankConnection $connection): void
    {
        foreach ([[1, 'success', 14, null], [2, 'success', 9, null], [3, 'failed', 0, 'Mono API timed out after 3 retries.'], [4, 'success', 21, null]] as [$daysAgo, $status, $count, $message]) {
            SyncLog::factory()->for($user)->create([
                'bank_connection_id' => $connection->id,
                'status' => $status,
                'message' => $message,
                'transactions_synced' => $count,
                'started_at' => now()->subDays($daysAgo)->subMinutes(2),
                'finished_at' => now()->subDays($daysAgo),
            ]);
        }
    }

    private function seedNotifications(User $user, Goal $emergencyFund, Goal $completedGoal): void
    {
        $notifications = [
            [RuleExecuted::class, [
                'rule_id' => $emergencyFund->rules()->value('id'),
                'allocation_id' => $emergencyFund->allocations()->value('id'),
                'message' => 'Rule "Save 20% of salary" allocated 170,000.00 to Emergency Fund.',
            ], null, now()->subDays(1)],
            [LargeSpendingAlert::class, [
                'transaction_id' => Transaction::where('user_id', $user->id)->where('amount', 315000)->value('id'),
                'amount' => '315000.00',
                'message' => 'A debit of 315,000.00 on '.now()->subDays(3)->format('M j, Y').' is significantly higher than your recent average.',
            ], null, now()->subDays(3)],
            [GoalUpdated::class, [
                'goal_id' => $emergencyFund->id,
                'goal_name' => $emergencyFund->name,
                'amount' => '170000.00',
                'message' => 'You allocated 170,000.00 to Emergency Fund. Current progress: '.round($emergencyFund->current_amount / $emergencyFund->target_amount * 100).'%.',
            ], now()->subDays(2), now()->subDays(4)],
            [GoalCompleted::class, [
                'goal_id' => $completedGoal->id,
                'goal_name' => $completedGoal->name,
                'message' => "You've reached your goal \"{$completedGoal->name}\"!",
            ], now()->subWeeks(1), now()->subWeeks(2)],
            [SyncErrorAlert::class, [
                'message' => 'We could not sync your GTBank connection. Mono API timed out after 3 retries.',
            ], now()->subDays(2), now()->subDays(3)],
        ];

        foreach ($notifications as [$type, $data, $readAt, $createdAt]) {
            $user->notifications()->create([
                'id' => (string) Str::uuid(),
                'type' => $type,
                'data' => $data,
                'read_at' => $readAt,
                'created_at' => $createdAt,
            ]);
        }
    }
}
