<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('logging in records an audit log entry', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    expect(AuditLog::where('user_id', $user->id)->where('action', 'login')->exists())->toBeTrue();
});

test('changing the password records an audit log entry', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/settings/password', [
        'current_password' => 'password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    expect(AuditLog::where('user_id', $user->id)->where('action', 'password_changed')->exists())->toBeTrue();
});

test('requesting a password reset records an audit log entry', function () {
    $user = User::factory()->create();

    Notification::fake();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        return true;
    });

    expect(AuditLog::where('user_id', $user->id)->where('action', 'password_reset')->exists())->toBeTrue();
});
