<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
});

// ==========================================
// Register: Email Verification Event
// ==========================================

describe('Register Email Verification', function () {
    test('registration dispatches Registered event', function () {
        Event::fake([Registered::class]);

        $this->postJson('/api/register', [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => '@Password123',
            'password_confirmation' => '@Password123',
        ]);

        Event::assertDispatched(Registered::class);
    });

    test('registered user has null email_verified_at', function () {
        Event::fake([Registered::class]);

        $this->postJson('/api/register', [
            'name' => 'Unverified User',
            'email' => 'unverified@example.com',
            'password' => '@Password123',
            'password_confirmation' => '@Password123',
        ]);

        $user = User::where('email', 'unverified@example.com')->first();
        expect($user->email_verified_at)->toBeNull();
    });

    test('registration response includes verification message', function () {
        Event::fake([Registered::class]);

        $response = $this->postJson('/api/register', [
            'name' => 'Verify Me',
            'email' => 'verifyme@example.com',
            'password' => '@Password123',
            'password_confirmation' => '@Password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        expect($response->json('message'))->toContain('verifikasi');
    });
});

// ==========================================
// Verify Email Endpoint
// ==========================================

describe('Email Verification Endpoint', function () {
    test('user can verify email with valid signed url', function () {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        // Extract path dari URL
        $path = parse_url($verificationUrl, PHP_URL_PATH);
        $query = parse_url($verificationUrl, PHP_URL_QUERY);

        $response = $this->get("{$path}?{$query}");

        $response->assertRedirect();

        $user->refresh();
        expect($user->hasVerifiedEmail())->toBeTrue();
    });

    test('already verified user gets appropriate message', function () {
        $user = User::factory()->create(); // Already verified

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        $path = parse_url($verificationUrl, PHP_URL_PATH);
        $query = parse_url($verificationUrl, PHP_URL_QUERY);

        $response = $this->getJson("{$path}?{$query}");

        $response->assertOk()
            ->assertJsonPath('message', 'Email sudah diverifikasi sebelumnya');
    });

    test('invalid hash is rejected', function () {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => 'invalid-hash']
        );

        $path = parse_url($verificationUrl, PHP_URL_PATH);
        $query = parse_url($verificationUrl, PHP_URL_QUERY);

        $response = $this->getJson("{$path}?{$query}");

        $response->assertForbidden();
    });

    test('unsigned request is rejected', function () {
        $user = User::factory()->unverified()->create();
        $hash = sha1($user->getEmailForVerification());

        // Tanpa signature
        $response = $this->getJson("/api/email/verify/{$user->id}/{$hash}");

        $response->assertForbidden();
    });
});

// ==========================================
// Resend Verification
// ==========================================

describe('Resend Verification Email', function () {
    test('unverified user can request resend', function () {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/email/resend');

        $response->assertOk()
            ->assertJsonPath('success', true);

        Notification::assertSentTo($user, VerifyEmail::class);
    });

    test('already verified user gets appropriate message', function () {
        Notification::fake();

        $user = User::factory()->create(); // Verified

        $response = $this->actingAs($user)
            ->postJson('/api/email/resend');

        $response->assertOk()
            ->assertJsonPath('message', 'Email sudah diverifikasi');

        Notification::assertNotSentTo($user, VerifyEmail::class);
    });

    test('unauthenticated cannot resend', function () {
        $response = $this->postJson('/api/email/resend');

        $response->assertUnauthorized();
    });
});

// ==========================================
// Admin Auto-Verification
// ==========================================

describe('Admin Auto-Verification', function () {
    test('admin created by super_admin is automatically verified', function () {
        $branch = \App\Models\Branch::factory()->create();

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/admins', [
                'name' => 'Admin Baru',
                'email' => 'adminbaru@test.com',
                'password' => '@Password123',
                'password_confirmation' => '@Password123',
                'branch_id' => $branch->id,
            ]);

        $response->assertCreated();

        $admin = User::where('email', 'adminbaru@test.com')->first();
        expect($admin->hasVerifiedEmail())->toBeTrue();
    });
});
