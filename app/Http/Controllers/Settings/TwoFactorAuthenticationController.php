<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Show the user's two-factor authentication settings page.
     *
     * The enable/confirm/disable/recovery-code actions themselves are handled
     * by Laravel Fortify's own routes (two-factor.enable, two-factor.confirm,
     * two-factor.disable, two-factor.qr-code, two-factor.secret-key,
     * two-factor.recovery-codes) — this controller only renders the page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('settings/two-factor', [
            'twoFactorEnabled' => $user->hasEnabledTwoFactorAuthentication(),
            'twoFactorConfirming' => ! is_null($user->two_factor_secret) && is_null($user->two_factor_confirmed_at),
            'status' => $request->session()->get('status'),
        ]);
    }
}
