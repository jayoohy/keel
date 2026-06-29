<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\LargeSpendingAlert;
use App\Services\AI\AiTransactionCategorizer;
use App\Services\AI\AnomalyDetector;
use App\Services\AI\InsightGenerator;
use Illuminate\Console\Command;

class GenerateInsights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-insights';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Categorize uncategorized transactions and generate spending/goal/anomaly insights via OpenAI';

    /**
     * Execute the console command.
     *
     * Runs as a scheduled batch (PRD §9.5) — never called synchronously on
     * a user request, since OpenAI calls are too slow for the request
     * cycle on shared hosting.
     */
    public function handle(
        AiTransactionCategorizer $categorizer,
        InsightGenerator $insights,
        AnomalyDetector $anomalyDetector,
    ): void {
        User::query()->each(function (User $user) use ($categorizer, $insights, $anomalyDetector) {
            $categorizer->categorizeUncategorized($user);

            $insights->generateSpendingInsight($user);

            $user->goals()->where('status', 'active')->each(
                fn ($goal) => $insights->generateGoalProgressInsight($goal)
            );

            $anomalyDetector->detect($user)->each(function ($transaction) use ($insights, $user) {
                if ($insights->generateAnomalyInsight($transaction)) {
                    $user->notify(new LargeSpendingAlert($transaction));
                }
            });
        });
    }
}
