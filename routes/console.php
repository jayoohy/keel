<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Confirms the single Hostinger cron job (`* * * * * php artisan schedule:run`)
// is actually reaching the scheduler. Check the log after deploy.
Artisan::command('heartbeat', function () {
    Log::info('Scheduler heartbeat', ['ran_at' => now()->toDateTimeString()]);
})->purpose('Verify the Hostinger cron job is triggering the scheduler');

Schedule::command('heartbeat')->everyMinute();

// Hostinger shared hosting cannot run a persistent `queue:work` process, so queued
// jobs are drained in short batches on every scheduler tick instead. Real-world
// cadence is floored by the host's cron interval (often ~5 minutes on basic plans).
Schedule::command('queue:work --stop-when-empty --max-time=50')
    ->everyMinute()
    ->withoutOverlapping();

// Real-time bank sync isn't possible on this hosting tier — connections are
// re-synced on this schedule instead (see PRD §7).
Schedule::command('app:sync-bank-connections')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

// Goal forecasts and the financial health score are recomputed on this
// schedule rather than per page load (PRD §4.9/§4.10).
Schedule::command('app:compute-forecasts')
    ->everyThirtyMinutes()
    ->withoutOverlapping();

// AI insights (categorization fallback, spending/goal/anomaly insights) only
// ever run here — never synchronously on a request (PRD §4.11 #41/§9.5).
Schedule::command('app:generate-insights')
    ->daily()
    ->withoutOverlapping();
