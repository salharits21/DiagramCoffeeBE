<?php

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'customer@test.com',
        'password' => Hash::make('@OldPassword123'),
    ]);
});

// ==========================================
// Forgot Password: Kirim Link Reset
// ==========================================

describe('Forgot Password', function () {
    test('can request password reset link', function () {
        Notification::fake();

        $response = $this->postJson('/api/forgot-password', [
            'email' => 'customer@test.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        Notification::assertSentTo($this->user, ResetPassword::class);
    });

    test('returns error for non-existent email', function () {
        Notification::fake();

        $response = $this->postJson('/api/forgot-password', [
            'email' => 'notexist@test.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);

        Notification::assertNothingSent();
    });

    test('email is required', function () {
        $response = $this->postJson('/api/forgot-password', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    test('email must be valid format', function () {
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'bukan-email',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });
});

// ==========================================
// Reset Password: Ubah Password
// ==========================================

describe('Reset Password', function () {
    test('can reset password with valid token', function () {
        $token = Password::createToken($this->user);

        $response = $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'customer@test.com',
            'password' => '@NewPassword456',
            'password_confirmation' => '@NewPassword456',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Pastikan password berubah
        $this->user->refresh();
        expect(Hash::check('@NewPassword456', $this->user->password))->toBeTrue();
    });

    test('can login with new password after reset', function () {
        $token = Password::createToken($this->user);

        $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'customer@test.com',
            'password' => '@NewPassword456',
            'password_confirmation' => '@NewPassword456',
        ]);

        // Verifikasi password lama tidak berlaku, password baru berlaku
        $this->user->refresh();
        expect(Hash::check('@OldPassword123', $this->user->password))->toBeFalse()
            ->and(Hash::check('@NewPassword456', $this->user->password))->toBeTrue();
    });

    test('cannot reset with invalid token', function () {
        $response = $this->postJson('/api/reset-password', [
            'token' => 'invalid-token',
            'email' => 'customer@test.com',
            'password' => '@NewPassword456',
            'password_confirmation' => '@NewPassword456',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    });

    test('cannot reset with wrong email', function () {
        $token = Password::createToken($this->user);

        $response = $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'wrong@test.com',
            'password' => '@NewPassword456',
            'password_confirmation' => '@NewPassword456',
        ]);

        $response->assertUnprocessable();
    });

    test('password must meet complexity requirements', function () {
        $token = Password::createToken($this->user);

        $response = $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'customer@test.com',
            'password' => 'weakpassword',
            'password_confirmation' => 'weakpassword',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    });

    test('password confirmation must match', function () {
        $token = Password::createToken($this->user);

        $response = $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'customer@test.com',
            'password' => '@NewPassword456',
            'password_confirmation' => '@DifferentPassword789',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    });

    test('token is required', function () {
        $response = $this->postJson('/api/reset-password', [
            'email' => 'customer@test.com',
            'password' => '@NewPassword456',
            'password_confirmation' => '@NewPassword456',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['token']);
    });

    test('token can only be used once', function () {
        $token = Password::createToken($this->user);

        // First reset — success
        $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'customer@test.com',
            'password' => '@NewPassword456',
            'password_confirmation' => '@NewPassword456',
        ])->assertOk();

        // Second reset with same token — fail
        $response = $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'customer@test.com',
            'password' => '@AnotherPassword789',
            'password_confirmation' => '@AnotherPassword789',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    });
});
