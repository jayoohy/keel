<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PreferencesController extends Controller
{
    /**
     * The notification types a user can opt in/out of, per the PRD's
     * notification requirements. Missing keys in a user's stored preferences
     * default to enabled.
     */
    public const NOTIFICATION_TYPES = [
        'goal_updates' => 'Goal updates',
        'large_spending_alerts' => 'Large spending alerts',
        'goal_completion' => 'Goal completion',
        'sync_errors' => 'Sync errors',
        'rule_executions' => 'Rule executions',
    ];

    public function edit(Request $request): Response
    {
        $stored = $request->user()->notification_preferences ?? [];

        $notificationPreferences = collect(self::NOTIFICATION_TYPES)
            ->keys()
            ->mapWithKeys(fn (string $key) => [$key => $stored[$key] ?? true]);

        return Inertia::render('settings/preferences', [
            'currency' => $request->user()->currency,
            'notificationTypes' => self::NOTIFICATION_TYPES,
            'notificationPreferences' => $notificationPreferences,
            'status' => $request->session()->get('status'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'notification_preferences' => ['required', 'array'],
            'notification_preferences.*' => ['boolean'],
        ]);

        $request->user()->update([
            'notification_preferences' => $validated['notification_preferences'],
        ]);

        return back()->with('status', 'preferences-updated');
    }
}
