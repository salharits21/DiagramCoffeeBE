<?php

use App\Models\User;
use App\Models\Category;
use App\Models\MenuItem;

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->customer = User::factory()->create(['role' => 'customer']);
});

// ==========================================
// Public Routes
// ==========================================

describe('Public Category Endpoints', function () {
    test('anyone can list categories', function () {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data');
    });

    test('categories include active menu item count', function () {
        $category = Category::factory()->create();
        MenuItem::factory()->count(3)->create(['category_id' => $category->id, 'is_active' => true]);
        MenuItem::factory()->create(['category_id' => $category->id, 'is_active' => false]);

        $response = $this->getJson('/api/categories');

        $response->assertOk()
            ->assertJsonPath('data.0.menu_items_count', 3);
    });

    test('anyone can view a category with its menu items', function () {
        $category = Category::factory()->create();
        MenuItem::factory()->count(2)->create(['category_id' => $category->id, 'is_active' => true]);
        MenuItem::factory()->create(['category_id' => $category->id, 'is_active' => false]);

        $response = $this->getJson("/api/categories/{$category->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.menu_items'); // Hanya yang aktif
    });
});

// ==========================================
// Super Admin CRUD
// ==========================================

describe('Super Admin Category Management', function () {
    test('super admin can create a category', function () {
        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/categories', [
                'name' => 'New Category',
                'description' => 'Test description',
                'sort_order' => 5,
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'New Category')
            ->assertJsonPath('data.slug', 'new-category');

        $this->assertDatabaseHas('categories', ['slug' => 'new-category']);
    });

    test('slug is auto-generated and unique', function () {
        Category::factory()->create(['name' => 'Coffee', 'slug' => 'coffee']);

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/categories', [
                'name' => 'Coffee',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.slug', 'coffee-1');
    });

    test('super admin can update a category', function () {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/admin/categories/{$category->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.slug', 'updated-name');
    });

    test('super admin can delete a category (soft delete)', function () {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->superAdmin)
            ->deleteJson("/api/admin/categories/{$category->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    });

    test('create category validation fails without name', function () {
        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/categories', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });
});

// ==========================================
// Access Control
// ==========================================

describe('Category Access Control', function () {
    test('admin cannot create a category', function () {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/categories', ['name' => 'Test']);

        $response->assertForbidden();
    });

    test('customer cannot create a category', function () {
        $response = $this->actingAs($this->customer)
            ->postJson('/api/admin/categories', ['name' => 'Test']);

        $response->assertForbidden();
    });
});
