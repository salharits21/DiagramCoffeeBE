<?php

use App\Models\User;
use App\Models\AppSetting;

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->customer = User::factory()->create(['role' => 'customer']);

    AppSetting::create(['key' => 'admin_fee', 'value' => '2000.00', 'label' => 'Biaya Admin']);
});

describe('Fee Settings - Index', function () {

    test('super admin dapat melihat semua pengaturan fee', function () {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/admin/settings/fee');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');

        expect($response->json('data.0.key'))->toBe('admin_fee')
            ->and($response->json('data.0.value'))->toBe('2000.00')
            ->and($response->json('data.0.label'))->toBe('Biaya Admin');
    });

    test('admin biasa tidak bisa mengakses pengaturan fee', function () {
        $this->actingAs($this->admin)
            ->getJson('/api/admin/settings/fee')
            ->assertForbidden();
    });

    test('customer tidak bisa mengakses pengaturan fee', function () {
        $this->actingAs($this->customer)
            ->getJson('/api/admin/settings/fee')
            ->assertForbidden();
    });
});

describe('Fee Settings - Store', function () {

    test('super admin dapat menambahkan fee baru (fixed)', function () {
        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/settings/fee', [
                'key' => 'service_charge',
                'value' => '5000',
                'label' => 'Biaya Layanan',
                'type' => 'fixed',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.key', 'service_charge')
            ->assertJsonPath('data.value', '5000')
            ->assertJsonPath('data.label', 'Biaya Layanan')
            ->assertJsonPath('data.type', 'fixed');

        $this->assertDatabaseHas('app_settings', ['key' => 'service_charge', 'type' => 'fixed']);
    });

    test('super admin dapat menambahkan fee baru (percentage)', function () {
        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/settings/fee', [
                'key' => 'pb1',
                'value' => '10',
                'label' => 'Pajak Restoran',
                'type' => 'percentage',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.key', 'pb1')
            ->assertJsonPath('data.value', '10')
            ->assertJsonPath('data.type', 'percentage');

        $this->assertDatabaseHas('app_settings', ['key' => 'pb1', 'type' => 'percentage']);
    });

    test('validasi percentage fee tidak boleh lebih dari 100', function () {
        $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/settings/fee', [
                'key' => 'tax',
                'value' => '101',
                'label' => 'Pajak',
                'type' => 'percentage',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['value']);
    });

    test('tidak bisa menambahkan fee dengan key duplikat', function () {
        $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/settings/fee', [
                'key' => 'admin_fee',
                'value' => '3000',
                'label' => 'Duplikat',
            ])
            ->assertUnprocessable();
    });

    test('validasi store fee: key, value, label, dan type wajib diisi', function () {
        $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/settings/fee', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['key', 'value', 'label', 'type']);
    });

    test('validasi store fee: value harus numerik', function () {
        $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/settings/fee', [
                'key' => 'tax',
                'value' => 'bukan_angka',
                'label' => 'Pajak',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['value']);
    });
});

describe('Fee Settings - Update', function () {

    test('super admin dapat mengubah fee yang ada', function () {
        $response = $this->actingAs($this->superAdmin)
            ->putJson('/api/admin/settings/fee/admin_fee', [
                'value' => '3500',
                'label' => 'Biaya Admin (Baru)',
                'type' => 'fixed',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.value', '3500')
            ->assertJsonPath('data.label', 'Biaya Admin (Baru)');

        expect(AppSetting::getValue('admin_fee'))->toBe('3500');
    });

    test('update fee yang tidak ada mengembalikan 404', function () {
        $this->actingAs($this->superAdmin)
            ->putJson('/api/admin/settings/fee/nonexistent', [
                'value' => '1000',
                'label' => 'Test',
                'type' => 'fixed',
            ])
            ->assertNotFound();
    });

    test('validasi update fee: value, label, dan type wajib diisi', function () {
        $this->actingAs($this->superAdmin)
            ->putJson('/api/admin/settings/fee/admin_fee', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['value', 'label', 'type']);
    });

    test('validasi update fee: value harus numerik', function () {
        $this->actingAs($this->superAdmin)
            ->putJson('/api/admin/settings/fee/admin_fee', [
                'value' => 'abc',
                'label' => 'Test',
            ])
            ->assertUnprocessable();
    });
});

describe('Fee Settings - Delete', function () {

    test('super admin dapat menghapus fee', function () {
        AppSetting::create(['key' => 'pph', 'value' => '1000', 'label' => 'PPh']);

        $response = $this->actingAs($this->superAdmin)
            ->deleteJson('/api/admin/settings/fee/pph');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Fee berhasil dihapus');

        $this->assertDatabaseMissing('app_settings', ['key' => 'pph']);
    });

    test('hapus fee yang tidak ada mengembalikan 404', function () {
        $this->actingAs($this->superAdmin)
            ->deleteJson('/api/admin/settings/fee/nonexistent')
            ->assertNotFound();
    });
});
