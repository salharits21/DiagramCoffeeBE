<?php

use App\Models\User;
use App\Models\Voucher;
use App\Models\UserVoucher;

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->customer = User::factory()->create(['role' => 'customer', 'loyalty_points' => 100]);

    $this->voucher = Voucher::create([
        'name' => 'Diskon 10K',
        'code' => 'DISC10K',
        'discount_amount' => 10000,
        'min_transaction_amount' => 50000,
        'points_required' => 50,
        'is_active' => true,
    ]);
});

// ==========================================
// Admin CRUD Tests
// ==========================================
describe('Voucher CRUD (Super Admin)', function () {
    test('super admin can view vouchers', function () {
        $response = $this->actingAs($this->customer)->getJson('/api/vouchers');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    });

    test('super admin can create voucher', function () {
        $response = $this->actingAs($this->superAdmin)->postJson('/api/admin/vouchers', [
            'name' => 'Diskon 20K',
            'code' => 'DISC20K',
            'discount_amount' => 20000,
            'min_transaction_amount' => 100000,
            'points_required' => 100,
            'is_active' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);
            
        $this->assertDatabaseHas('vouchers', ['code' => 'DISC20K']);
    });

    test('admin cannot create voucher', function () {
        $this->actingAs($this->admin)->postJson('/api/admin/vouchers', [
            'name' => 'Diskon 20K',
            'code' => 'DISC20K',
            'discount_amount' => 20000,
            'min_transaction_amount' => 100000,
            'points_required' => 100,
            'is_active' => true,
        ])->assertForbidden();
    });

    test('customer cannot create voucher', function () {
        $this->actingAs($this->customer)->postJson('/api/admin/vouchers', [
            'name' => 'Diskon 20K',
            'code' => 'DISC20K',
            'discount_amount' => 20000,
            'min_transaction_amount' => 100000,
            'points_required' => 100,
            'is_active' => true,
        ])->assertForbidden();
    });

    test('super admin can update voucher', function () {
        $response = $this->actingAs($this->superAdmin)->putJson("/api/admin/vouchers/{$this->voucher->id}", [
            'discount_amount' => 15000,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);
            
        $this->assertDatabaseHas('vouchers', [
            'id' => $this->voucher->id,
            'discount_amount' => 15000,
        ]);
    });

    test('super admin can delete voucher', function () {
        $response = $this->actingAs($this->superAdmin)->deleteJson("/api/admin/vouchers/{$this->voucher->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('vouchers', ['id' => $this->voucher->id]);
    });
});

// ==========================================
// Customer Exchange Tests
// ==========================================
describe('Voucher Exchange', function () {
    test('customer can exchange points for voucher if enough points', function () {
        $response = $this->actingAs($this->customer)->postJson('/api/vouchers/exchange', [
            'voucher_id' => $this->voucher->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Poin harus berkurang 50
        $this->customer->refresh();
        expect($this->customer->loyalty_points)->toBe(50);

        $this->assertDatabaseHas('user_vouchers', [
            'user_id' => $this->customer->id,
            'voucher_id' => $this->voucher->id,
            'is_used' => false,
        ]);
    });

    test('customer cannot exchange if points are insufficient', function () {
        // Kurangi poin jadi 10
        $this->customer->update(['loyalty_points' => 10]);

        $response = $this->actingAs($this->customer)->postJson('/api/vouchers/exchange', [
            'voucher_id' => $this->voucher->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['points']);

        $this->customer->refresh();
        expect($this->customer->loyalty_points)->toBe(10); // Tetap
    });

    test('customer can view their own vouchers', function () {
        // Beri 1 voucher
        UserVoucher::create([
            'user_id' => $this->customer->id,
            'voucher_id' => $this->voucher->id,
            'is_used' => false,
            'expired_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($this->customer)->getJson('/api/vouchers/my-vouchers');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    });
});
