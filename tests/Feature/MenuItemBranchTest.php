<?php

use App\Models\User;
use App\Models\Branch;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\MenuItemBranch;

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
    $this->category = Category::factory()->create();
    $this->branch = Branch::factory()->create();
    $this->admin = User::factory()->create([
        'role' => 'admin',
        'branch_id' => $this->branch->id,
    ]);
    $this->otherBranch = Branch::factory()->create();
    $this->otherAdmin = User::factory()->create([
        'role' => 'admin',
        'branch_id' => $this->otherBranch->id,
    ]);
    $this->customer = User::factory()->create(['role' => 'customer']);
    $this->menuItem = MenuItem::factory()->create(['category_id' => $this->category->id]);
});

// ==========================================
// Assign / Unassign
// ==========================================

describe('Menu-Branch Assignment', function () {
    test('super admin can assign menu to branch', function () {
        $response = $this->actingAs($this->superAdmin)
            ->postJson("/api/admin/branches/{$this->branch->id}/menu-items/{$this->menuItem->id}");

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('menu_item_branch', [
            'menu_item_id' => $this->menuItem->id,
            'branch_id' => $this->branch->id,
        ]);
    });

    test('duplicate assign returns 409', function () {
        MenuItemBranch::factory()->create([
            'menu_item_id' => $this->menuItem->id,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->postJson("/api/admin/branches/{$this->branch->id}/menu-items/{$this->menuItem->id}");

        $response->assertStatus(409);
    });

    test('super admin can unassign menu from branch', function () {
        MenuItemBranch::factory()->create([
            'menu_item_id' => $this->menuItem->id,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->deleteJson("/api/admin/branches/{$this->branch->id}/menu-items/{$this->menuItem->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('menu_item_branch', [
            'menu_item_id' => $this->menuItem->id,
            'branch_id' => $this->branch->id,
        ]);
    });

    test('admin cannot assign menu to branch', function () {
        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/branches/{$this->branch->id}/menu-items/{$this->menuItem->id}");

        $response->assertForbidden();
    });
});

// ==========================================
// Stock & Promo Update
// ==========================================

describe('Stock and Promo Management', function () {
    beforeEach(function () {
        $this->pivot = MenuItemBranch::factory()->create([
            'menu_item_id' => $this->menuItem->id,
            'branch_id' => $this->branch->id,
            'is_available' => true,
            'stock' => 20,
        ]);
    });

    test('super admin can view stock for a branch', function () {
        $response = $this->actingAs($this->superAdmin)
            ->getJson("/api/admin/branches/{$this->branch->id}/stock");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    });

    test('admin can view stock for own branch', function () {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/admin/branches/{$this->branch->id}/stock");

        $response->assertOk();
    });

    test('admin cannot view stock for other branch', function () {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/admin/branches/{$this->otherBranch->id}/stock");

        $response->assertForbidden();
    });

    test('admin can update stock on own branch', function () {
        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/branches/{$this->branch->id}/menu-items/{$this->menuItem->id}/stock", [
                'stock' => 50,
                'is_available' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('menu_item_branch', [
            'id' => $this->pivot->id,
            'stock' => 50,
        ]);
    });

    test('admin cannot update stock on other branch', function () {
        $otherPivot = MenuItemBranch::factory()->create([
            'menu_item_id' => $this->menuItem->id,
            'branch_id' => $this->otherBranch->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/branches/{$this->otherBranch->id}/menu-items/{$this->menuItem->id}/stock", [
                'stock' => 10,
            ]);

        $response->assertForbidden();
    });

    test('can set percentage discount promo', function () {
        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/admin/branches/{$this->branch->id}/menu-items/{$this->menuItem->id}/stock", [
                'discount_type' => 'percentage',
                'discount_percentage' => 15.00,
                'is_promo_active' => true,
            ]);

        $response->assertOk();

        $this->pivot->refresh();
        expect($this->pivot->discount_type)->toBe('percentage')
            ->and($this->pivot->discount_percentage)->toBe('15.00')
            ->and($this->pivot->discount_amount)->toBeNull()
            ->and($this->pivot->is_promo_active)->toBeTrue();
    });

    test('can set fixed discount promo', function () {
        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/admin/branches/{$this->branch->id}/menu-items/{$this->menuItem->id}/stock", [
                'discount_type' => 'fixed',
                'discount_amount' => 5000.00,
                'is_promo_active' => true,
            ]);

        $response->assertOk();

        $this->pivot->refresh();
        expect($this->pivot->discount_type)->toBe('fixed')
            ->and($this->pivot->discount_percentage)->toBeNull()
            ->and($this->pivot->discount_amount)->toBe('5000.00')
            ->and($this->pivot->is_promo_active)->toBeTrue();
    });

    test('switching discount type clears the other field', function () {
        $this->pivot->update([
            'discount_type' => 'percentage',
            'discount_percentage' => 10.00,
            'is_promo_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/admin/branches/{$this->branch->id}/menu-items/{$this->menuItem->id}/stock", [
                'discount_type' => 'fixed',
                'discount_amount' => 3000.00,
            ]);

        $response->assertOk();

        $this->pivot->refresh();
        expect($this->pivot->discount_type)->toBe('fixed')
            ->and($this->pivot->discount_percentage)->toBeNull()
            ->and($this->pivot->discount_amount)->toBe('3000.00');
    });

    test('customer cannot update stock', function () {
        $response = $this->actingAs($this->customer)
            ->putJson("/api/admin/branches/{$this->branch->id}/menu-items/{$this->menuItem->id}/stock", [
                'stock' => 10,
            ]);

        $response->assertForbidden();
    });
});
