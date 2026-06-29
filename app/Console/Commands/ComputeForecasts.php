<?php

namespace App\Console\Commands;

use App\Models\Goal;
use App\Models\User;
use App\Services\Forecasting\ForecastService;
use App\Services\Forecasting\HealthScoreService;
use Illuminate\Console\Command;

class ComputeForecasts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:compute-forecasts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recompute goal forecasts and financial health scores for every user';

    /**
     * Execute the console command.
     */
    public function handle(ForecastService $forecastService, HealthScoreService $healthScoreService): void
    {
        Goal::where('status', 'active')->each(fn (Goal $goal) => $forecastService->forecastGoal($goal));

        User::query()->each(fn (User $user) => $healthScoreService->compute($user));
    }
}
