<?php

use App\Models\User;
use App\Models\Branch;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\MenuItemBranch;
use App\Models\Order;
use App\Models\OrderItem;

beforeEach(function () {
    $this->branch1 = Branch::factory()->create(['name' => 'Cabang A']);
    $this->branch2 = Branch::factory()->create(['name' => 'Cabang B']);
    $this->category = Category::factory()->create();

    $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
    $this->admin = User::factory()->create(['role' => 'admin', 'branch_id' => $this->branch1->id]);
    $this->customer = User::factory()->create(['role' => 'customer']);

    $this->menu1 = MenuItem::factory()->create(['category_id' => $this->category->id, 'name' => 'Espresso', 'base_price' => 22000]);
    $this->menu2 = MenuItem::factory()->create(['category_id' => $this->category->id, 'name' => 'Latte', 'base_price' => 32000]);

    MenuItemBranch::factory()->create(['menu_item_id' => $this->menu1->id, 'branch_id' => $this->branch1->id]);
    MenuItemBranch::factory()->create(['menu_item_id' => $this->menu2->id, 'branch_id' => $this->branch1->id]);

    // Create completed orders for branch 1 (today)
    $order1 = Order::factory()->create([
        'user_id' => $this->customer->id,
        'branch_id' => $this->branch1->id,
        'status' => 'completed',
        'total_amount' => 56000,
        'created_at' => now(),
    ]);
    OrderItem::factory()->create(['order_id' => $order1->id, 'menu_item_id' => $this->menu1->id, 'menu_item_name' => 'Espresso', 'quantity' => 2, 'subtotal' => 44000]);
    OrderItem::factory()->create(['order_id' => $order1->id, 'menu_item_id' => $this->menu2->id, 'menu_item_name' => 'Latte', 'quantity' => 1, 'subtotal' => 32000]);

    // Create completed order for branch 2 (today)
    $order2 = Order::factory()->create([
        'user_id' => $this->customer->id,
        'branch_id' => $this->branch2->id,
        'status' => 'completed',
        'total_amount' => 32000,
        'created_at' => now(),
    ]);
    OrderItem::factory()->create(['order_id' => $order2->id, 'menu_item_id' => $this->menu2->id, 'menu_item_name' => 'Latte', 'quantity' => 1, 'subtotal' => 32000]);

    // Create completed order for branch 1 (3 days ago)
    $order3 = Order::factory()->create([
        'user_id' => $this->customer->id,
        'branch_id' => $this->branch1->id,
        'status' => 'completed',
        'total_amount' => 22000,
        'created_at' => now()->subDays(3),
    ]);
    OrderItem::factory()->create(['order_id' => $order3->id, 'menu_item_id' => $this->menu1->id, 'menu_item_name' => 'Espresso', 'quantity' => 1, 'subtotal' => 22000]);
});

describe('Statistik Penjualan', function () {

    test('customer tidak bisa mengakses statistik', function () {
        $this->actingAs($this->customer)
            ->getJson('/api/admin/statistics')
            ->assertForbidden();
    });

    test('admin melihat statistik cabangnya sendiri', function () {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/statistics?days=7');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $data = $response->json('data');

        // Hanya 1 order hari ini di branch 1
        expect($data['today_transactions'])->toBe(1)
            ->and((float) $data['today_revenue'])->toBe(56000.00);

        // daily_revenue harus ada 2 entri (hari ini & 3 hari lalu)
        expect($data['daily_revenue'])->toHaveCount(2);

        // Top menu: Espresso (qty 3) > Latte (qty 1) di branch 1
        expect($data['top_menus'][0]['menu_item_name'])->toBe('Espresso')
            ->and($data['top_menus'][0]['total_sold'])->toBe(3);
    });

    test('admin tidak bisa filter cabang lain', function () {
        // Admin branch 1 mencoba filter branch 2 — tetap melihat branch 1 saja
        $response = $this->actingAs($this->admin)
            ->getJson("/api/admin/statistics?days=7&branch_id={$this->branch2->id}");

        $response->assertOk();

        // Tetap melihat data branch 1 (admin scope override)
        expect($response->json('data.today_transactions'))->toBe(1);
    });

    test('super admin melihat statistik keseluruhan', function () {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/admin/statistics?days=7');

        $response->assertOk();

        $data = $response->json('data');

        // 2 orders hari ini (1 branch 1 + 1 branch 2)
        expect($data['today_transactions'])->toBe(2)
            ->and((float) $data['today_revenue'])->toBe(88000.00);
    });

    test('super admin filter per cabang', function () {
        $response = $this->actingAs($this->superAdmin)
            ->getJson("/api/admin/statistics?days=7&branch_id={$this->branch2->id}");

        $response->assertOk();

        $data = $response->json('data');

        // Hanya 1 order hari ini di branch 2
        expect($data['today_transactions'])->toBe(1)
            ->and((float) $data['today_revenue'])->toBe(32000.00);
    });

    test('days parameter max 30', function () {
        $this->actingAs($this->superAdmin)
            ->getJson('/api/admin/statistics?days=31')
            ->assertUnprocessable();
    });
});
