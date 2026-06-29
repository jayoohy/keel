<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Services\Allocation\AllocationEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GoalController extends Controller
{
    public function index(Request $request, AllocationEngine $allocationEngine): Response
    {
        return Inertia::render('goals/index', [
            'goals' => $request->user()->goals()->latest()->get(),
            'unallocatedBalance' => $allocationEngine->unallocatedBalance($request->user()),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('goals/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateGoal($request);

        $request->user()->goals()->create($validated);

        return to_route('goals.index');
    }

    public function show(Request $request, Goal $goal, AllocationEngine $allocationEngine): Response
    {
        $this->authorizeGoal($request, $goal);

        return Inertia::render('goals/show', [
            'goal' => $goal->load(['allocations', 'forecast']),
            'unallocatedBalance' => $allocationEngine->unallocatedBalance($request->user()),
        ]);
    }

    public function edit(Request $request, Goal $goal): Response
    {
        $this->authorizeGoal($request, $goal);

        return Inertia::render('goals/edit', ['goal' => $goal]);
    }

    public function update(Request $request, Goal $goal): RedirectResponse
    {
        $this->authorizeGoal($request, $goal);

        $validated = $this->validateGoal($request, includeStatus: true);

        $goal->update($validated);

        return to_route('goals.index');
    }

    private function validateGoal(Request $request, bool $includeStatus = false): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_amount' => ['required', 'numeric', 'min:1'],
            'deadline' => ['nullable', 'date'],
            'priority' => ['required', 'in:low,medium,high'],
            ...($includeStatus ? ['status' => ['required', 'in:active,completed,paused,cancelled']] : []),
        ]);
    }

    private function authorizeGoal(Request $request, Goal $goal): void
    {
        abort_unless($goal->user_id === $request->user()->id, 403);
    }
}
