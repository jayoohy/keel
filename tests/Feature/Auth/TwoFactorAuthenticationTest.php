<?php

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

function enableAndConfirmTwoFactor(User $user): string
{
    test()->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post('/user/two-factor-authentication');

    $code = app(Google2FA::class)->getCurrentOtp(decrypt($user->refresh()->two_factor_secret));

    test()->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post('/user/confirmed-two-factor-authentication', ['code' => $code]);

    return $code;
}

test('two-factor settings screen can be rendered', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings/two-factor');

    $response->assertStatus(200);
});

test('two-factor authentication can be enabled and confirmed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post('/user/two-factor-authentication');

    $user->refresh();
    expect($user->two_factor_secret)->not->toBeNull();
    expect($user->two_factor_confirmed_at)->toBeNull();

    $code = app(Google2FA::class)->getCurrentOtp(decrypt($user->two_factor_secret));

    $response = $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post('/user/confirmed-two-factor-authentication', ['code' => $code]);

    $response->assertSessionHasNoErrors();
    expect($user->refresh()->two_factor_confirmed_at)->not->toBeNull();
});

test('confirming two-factor authentication fails with an invalid code', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post('/user/two-factor-authentication');

    $response = $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post('/user/confirmed-two-factor-authentication', ['code' => '000000']);

    $response->assertSessionHasErrors();
    expect($user->refresh()->two_factor_confirmed_at)->toBeNull();
});

test('login challenges for a two-factor code once enabled', function () {
    $user = User::factory()->create();
    enableAndConfirmTwoFactor($user);

    auth()->logout();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('two-factor.login'));
});

test('a valid two-factor code completes the login challenge', function () {
    $user = User::factory()->create();
    enableAndConfirmTwoFactor($user);

    auth()->logout();

    // Google2FA's time-step counter is based on microtime(true), not Carbon, so
    // Laravel's time-travel helpers can't move it. Generate a code for the next
    // window explicitly instead, since the confirm step above already consumed
    // the current window's code (Fortify rejects resubmitting it as a replay).
    $engine = app(Google2FA::class);
    $code = $engine->oathTotp(decrypt($user->two_factor_secret), $engine->getTimestamp() + 1);

    // Seed the pending-login session state directly rather than depending on
    // cookie continuity between two separate test-client calls.
    $response = $this->withSession(['login.id' => $user->id, 'login.remember' => false])
        ->post('/two-factor-challenge', ['code' => $code]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('an invalid two-factor code does not complete the login challenge', function () {
    $user = User::factory()->create();
    enableAndConfirmTwoFactor($user);

    auth()->logout();

    $response = $this->withSession(['login.id' => $user->id, 'login.remember' => false])
        ->post('/two-factor-challenge', ['code' => '000000']);

    $this->assertGuest();
    $response->assertSessionHasErrors();
});

test('a recovery code completes the login challenge', function () {
    $user = User::factory()->create();
    enableAndConfirmTwoFactor($user);

    $recoveryCode = $user->refresh()->recoveryCodes()[0];

    auth()->logout();

    $response = $this->withSession(['login.id' => $user->id, 'login.remember' => false])
        ->post('/two-factor-challenge', ['recovery_code' => $recoveryCode]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));

    // The used recovery code is replaced and can't be reused.
    expect($user->refresh()->recoveryCodes())->not->toContain($recoveryCode);
});

test('two-factor authentication can be disabled', function () {
    $user = User::factory()->create();
    enableAndConfirmTwoFactor($user);

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->delete('/user/two-factor-authentication');

    expect($user->refresh()->two_factor_secret)->toBeNull();
});
