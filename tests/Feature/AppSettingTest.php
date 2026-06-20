<?php

use App\Models\User;
use App\Models\AppSetting;

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->customer = User::factory()->create(['role' => 'customer']);

    AppSetting::create(['key' => 'admin_fee', 'value' => '2000.00']);
});

describe('App Settings', function () {

    test('super admin dapat melihat semua pengaturan', function () {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/admin/settings');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');

        expect($response->json('data.0.key'))->toBe('admin_fee')
            ->and($response->json('data.0.value'))->toBe('2000.00');
    });

    test('super admin dapat mengubah biaya admin', function () {
        $response = $this->actingAs($this->superAdmin)
            ->putJson('/api/admin/settings/admin_fee', ['value' => '3000']);

        $response->assertOk()
            ->assertJsonPath('success', true);

        expect(AppSetting::getValue('admin_fee'))->toBe('3000');
    });

    test('validasi biaya admin harus numerik', function () {
        $this->actingAs($this->superAdmin)
            ->putJson('/api/admin/settings/admin_fee', ['value' => 'abc'])
            ->assertUnprocessable();
    });

    test('admin biasa tidak bisa mengakses pengaturan', function () {
        $this->actingAs($this->admin)
            ->getJson('/api/admin/settings')
            ->assertForbidden();
    });

    test('customer tidak bisa mengakses pengaturan', function () {
        $this->actingAs($this->customer)
            ->getJson('/api/admin/settings')
            ->assertForbidden();
    });

    test('update key yang tidak ada mengembalikan 404', function () {
        $this->actingAs($this->superAdmin)
            ->putJson('/api/admin/settings/nonexistent', ['value' => '123'])
            ->assertNotFound();
    });
});
