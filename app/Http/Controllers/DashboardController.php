<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardAggregator;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardAggregator $aggregator): Response
    {
        $user = $request->user();

        return Inertia::render('dashboard', [
            'summary' => $aggregator->summary($user),
            'spendingByCategory' => $aggregator->spendingByCategory($user),
            'trend' => $aggregator->incomeExpenseTrend($user),
            'goals' => $user->goals()->where('status', 'active')->latest()->take(3)->get(),
            'healthScore' => $user->financialHealthScores()->latest('computed_at')->first(),
            'insights' => $user->insights()->whereNull('dismissed_at')->latest()->take(5)->get(),
        ]);
    }
}
