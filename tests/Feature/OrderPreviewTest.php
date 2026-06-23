<?php

use App\Models\User;
use App\Models\Branch;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\MenuItemBranch;
use App\Models\AppSetting;
use App\Models\Voucher;
use App\Models\UserVoucher;

beforeEach(function () {
    $this->branch = Branch::create([
        'name' => 'Test Branch',
        'address' => 'Jl. Test',
        'phone' => '0811111111',
        'status' => 'active',
        'opening_time' => '08:00',
        'closing_time' => '22:00',
    ]);

    $category = Category::create(['name' => 'Coffee', 'slug' => 'coffee']);

    $this->menu1 = MenuItem::create([
        'category_id' => $category->id,
        'name' => 'Espresso',
        'slug' => 'espresso',
        'description' => 'Strong coffee',
        'base_price' => 22000,
        'is_active' => true,
    ]);

    $this->menu2 = MenuItem::create([
        'category_id' => $category->id,
        'name' => 'Latte',
        'slug' => 'latte',
        'description' => 'Smooth coffee',
        'base_price' => 30000,
        'is_active' => true,
    ]);

    MenuItemBranch::create([
        'menu_item_id' => $this->menu1->id,
        'branch_id' => $this->branch->id,
        'is_available' => true,
        'stock' => 50,
    ]);

    MenuItemBranch::create([
        'menu_item_id' => $this->menu2->id,
        'branch_id' => $this->branch->id,
        'is_available' => true,
        'stock' => 50,
        'discount_type' => 'percentage',
        'discount_percentage' => 10,
        'is_promo_active' => true,
    ]);

    $this->customer = User::factory()->create(['role' => 'customer']);

    AppSetting::create(['key' => 'admin_fee', 'value' => '2000.00', 'label' => 'Biaya Admin']);
});

describe('Order Preview - Guest', function () {

    test('guest dapat melihat preview transaksi', function () {
        $response = $this->postJson('/api/orders/preview', [
            'branch_id' => $this->branch->id,
            'items' => [
                ['menu_item_id' => $this->menu1->id, 'quantity' => 2],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Preview transaksi berhasil')
            ->assertJsonStructure([
                'data' => [
                    'items' => [['menu_item_id', 'name', 'quantity', 'base_price', 'unit_price', 'subtotal', 'discount']],
                    'subtotal',
                    'discount_total',
                    'voucher',
                    'fees',
                    'total_amount',
                    'loyalty_points_earned',
                ],
            ]);

        // Espresso: 22000 x 2 = 44000 + admin_fee 2000 = 46000
        expect($response->json('data.subtotal'))->toBe('44000.00')
            ->and($response->json('data.discount_total'))->toBe('0.00')
            ->and($response->json('data.total_amount'))->toBe('46000.00')
            ->and($response->json('data.loyalty_points_earned'))->toBe(0); // Guest tidak dapat poin
    });

    test('guest preview tidak mengurangi stok', function () {
        $this->postJson('/api/orders/preview', [
            'branch_id' => $this->branch->id,
            'items' => [
                ['menu_item_id' => $this->menu1->id, 'quantity' => 5],
            ],
        ])->assertOk();

        $stock = MenuItemBranch::where('menu_item_id', $this->menu1->id)
            ->where('branch_id', $this->branch->id)
            ->first()->stock;

        expect($stock)->toBe(50); // Stok tidak berubah
    });
});

describe('Order Preview - Customer', function () {

    test('customer dapat melihat preview dengan loyalty points', function () {
        $response = $this->actingAs($this->customer)
            ->postJson('/api/orders/preview', [
                'branch_id' => $this->branch->id,
                'items' => [
                    ['menu_item_id' => $this->menu1->id, 'quantity' => 2],
                    ['menu_item_id' => $this->menu2->id, 'quantity' => 1],
                ],
            ]);

        $response->assertOk();

        // Espresso: 22000 x 2 = 44000
        // Latte (base 30000, promo 10%): unit_price = 27000, subtotal = 27000
        // Subtotal = 22000*2 + 30000*1 = 74000 (base prices)
        // Discount = (30000-27000)*1 = 3000
        // After discount = 74000 - 3000 = 71000
        // + admin_fee 2000 = 73000
        // Loyalty = floor(73000/10000) = 7

        expect($response->json('data.subtotal'))->toBe('74000.00')
            ->and($response->json('data.discount_total'))->toBe('3000.00')
            ->and($response->json('data.total_amount'))->toBe('73000.00')
            ->and($response->json('data.loyalty_points_earned'))->toBe(7);
    });

    test('preview menampilkan rincian fee dari app_settings', function () {
        $response = $this->actingAs($this->customer)
            ->postJson('/api/orders/preview', [
                'branch_id' => $this->branch->id,
                'items' => [
                    ['menu_item_id' => $this->menu1->id, 'quantity' => 1],
                ],
            ]);

        $response->assertOk();

        $fees = $response->json('data.fees');
        expect($fees)->toHaveCount(1)
            ->and($fees[0]['key'])->toBe('admin_fee')
            ->and($fees[0]['label'])->toBe('Biaya Admin')
            ->and($fees[0]['amount'])->toBe('2000.00');
    });

    test('preview menampilkan rincian multiple fees (termasuk persentase)', function () {
        AppSetting::create(['key' => 'pph', 'value' => '10.00', 'label' => 'PPh 10%', 'type' => 'percentage']);

        $response = $this->actingAs($this->customer)
            ->postJson('/api/orders/preview', [
                'branch_id' => $this->branch->id,
                'items' => [
                    ['menu_item_id' => $this->menu1->id, 'quantity' => 1], // Espresso (22000)
                ],
            ]);

        $response->assertOk();

        $fees = collect($response->json('data.fees'));
        expect($fees)->toHaveCount(2);

        $adminFee = $fees->firstWhere('key', 'admin_fee');
        expect($adminFee['amount'])->toBe('2000.00');

        $pph = $fees->firstWhere('key', 'pph');
        // 10% dari 22000 = 2200
        expect($pph['amount'])->toBe('2200.00');

        // Total = 22000 + 2000 + 2200 = 26200
        expect($response->json('data.total_amount'))->toBe('26200.00');
    });


    test('preview dengan voucher menampilkan potongan voucher', function () {
        $this->customer->update(['loyalty_points' => 100]);

        $voucher = Voucher::create([
            'name' => 'Diskon 5rb',
            'code' => 'DISC5K',
            'discount_amount' => 5000,
            'min_transaction_amount' => 10000,
            'points_required' => 10,
            'is_active' => true,
        ]);

        UserVoucher::create([
            'user_id' => $this->customer->id,
            'voucher_id' => $voucher->id,
            'is_used' => false,
            'expired_at' => now()->addDays(30),
        ]);

        $userVoucher = UserVoucher::where('user_id', $this->customer->id)->first();

        $response = $this->actingAs($this->customer)
            ->postJson('/api/orders/preview', [
                'branch_id' => $this->branch->id,
                'items' => [
                    ['menu_item_id' => $this->menu1->id, 'quantity' => 2],
                ],
                'voucher_id' => $userVoucher->id,
            ]);

        $response->assertOk();

        // Subtotal: 44000, discount item: 0, voucher: 5000
        // After discount: 44000 - 5000 = 39000 + admin_fee 2000 = 41000
        expect($response->json('data.voucher.voucher_name'))->toBe('Diskon 5rb')
            ->and($response->json('data.voucher.voucher_code'))->toBe('DISC5K')
            ->and($response->json('data.voucher.voucher_discount'))->toBe('5000.00')
            ->and($response->json('data.discount_total'))->toBe('5000.00')
            ->and($response->json('data.total_amount'))->toBe('41000.00');

        // Voucher TIDAK ditandai used setelah preview
        expect($userVoucher->fresh()->is_used)->toBeFalse();
    });
});

describe('Order Preview - Validation', function () {

    test('branch_id wajib diisi', function () {
        $this->postJson('/api/orders/preview', [
            'items' => [['menu_item_id' => $this->menu1->id, 'quantity' => 1]],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['branch_id']);
    });

    test('items wajib diisi', function () {
        $this->postJson('/api/orders/preview', [
            'branch_id' => $this->branch->id,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);
    });

    test('menu yang tidak tersedia mengembalikan error', function () {
        MenuItemBranch::where('menu_item_id', $this->menu1->id)
            ->where('branch_id', $this->branch->id)
            ->update(['is_available' => false]);

        $this->postJson('/api/orders/preview', [
            'branch_id' => $this->branch->id,
            'items' => [['menu_item_id' => $this->menu1->id, 'quantity' => 1]],
        ])->assertUnprocessable();
    });

    test('stok tidak mencukupi mengembalikan error', function () {
        $this->postJson('/api/orders/preview', [
            'branch_id' => $this->branch->id,
            'items' => [['menu_item_id' => $this->menu1->id, 'quantity' => 999]],
        ])->assertUnprocessable();
    });
});
