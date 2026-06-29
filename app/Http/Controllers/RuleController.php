<?php

namespace App\Http\Controllers;

use App\Models\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RuleController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('rules/index', [
            'rules' => $request->user()->rules()->with('goal')->withCount('executions')->latest()->get(),
            'goals' => $request->user()->goals()->where('status', 'active')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('rules/create', [
            'goals' => $request->user()->goals()->where('status', 'active')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRule($request);

        $request->user()->rules()->create($validated);

        return to_route('rules.index');
    }

    public function show(Request $request, Rule $rule): Response
    {
        $this->authorizeRule($request, $rule);

        return Inertia::render('rules/show', [
            'rule' => $rule->load('goal'),
            'executions' => $rule->executions()->with('transaction', 'allocation')->latest('executed_at')->get(),
        ]);
    }

    public function edit(Request $request, Rule $rule): Response
    {
        $this->authorizeRule($request, $rule);

        return Inertia::render('rules/edit', [
            'rule' => $rule,
            'goals' => $request->user()->goals()->where('status', 'active')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Rule $rule): RedirectResponse
    {
        $this->authorizeRule($request, $rule);

        $rule->update($this->validateRule($request));

        return to_route('rules.index');
    }

    public function destroy(Request $request, Rule $rule): RedirectResponse
    {
        $this->authorizeRule($request, $rule);

        $rule->delete();

        return to_route('rules.index');
    }

    private function validateRule(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'goal_id' => ['required', 'integer', 'exists:goals,id'],
            'trigger_type' => ['required', 'in:transaction_type,category,merchant'],
            'trigger_value' => ['required', 'string', 'max:255'],
            'action_basis' => ['required', 'in:percentage,fixed'],
            'action_value' => ['required', 'numeric', 'min:0.01'],
            'is_active' => ['boolean'],
        ]);
    }

    private function authorizeRule(Request $request, Rule $rule): void
    {
        abort_unless($rule->user_id === $request->user()->id, 403);
    }
}
