<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AllocationController;
use App\Http\Controllers\BankConnectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\MonoWebhookController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RuleController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('bank-connections', [BankConnectionController::class, 'index'])->name('bank-connections.index');
    Route::post('bank-connections', [BankConnectionController::class, 'store'])->name('bank-connections.store');
    Route::delete('bank-connections/{bankConnection}', [BankConnectionController::class, 'destroy'])->name('bank-connections.destroy');

    Route::get('accounts', [AccountController::class, 'index'])->name('accounts.index');

    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::patch('transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');

    Route::resource('goals', GoalController::class)->except(['destroy']);

    Route::post('goals/{goal}/allocations', [AllocationController::class, 'store'])->name('allocations.store');
    Route::delete('allocations/{allocation}', [AllocationController::class, 'destroy'])->name('allocations.destroy');

    Route::resource('rules', RuleController::class);

    Route::get('insights', [InsightController::class, 'index'])->name('insights.index');
    Route::patch('insights/{insight}', [InsightController::class, 'update'])->name('insights.update');
    Route::delete('insights/{insight}', [InsightController::class, 'destroy'])->name('insights.destroy');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}', [NotificationController::class, 'update'])->name('notifications.update');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
});

Route::post('webhooks/mono', MonoWebhookController::class)->name('webhooks.mono');

require __DIR__.'/settings.php';
