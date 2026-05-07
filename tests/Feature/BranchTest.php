<?php

use App\Models\User;
use App\Models\Branch;

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->customer = User::factory()->create(['role' => 'customer']);
});

// ==========================================
// Public Routes
// ==========================================

describe('Public Branch Endpoints', function () {
    test('anyone can list active branches', function () {
        Branch::factory()->count(2)->create(['status' => 'active']);
        Branch::factory()->create(['status' => 'inactive']);

        $response = $this->getJson('/api/branches');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    });

    test('anyone can view a single branch', function () {
        $branch = Branch::factory()->create();

        $response = $this->getJson("/api/branches/{$branch->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', $branch->name);
    });
});

// ==========================================
// Super Admin CRUD
// ==========================================

describe('Super Admin Branch Management', function () {
    test('super admin can list all branches including inactive', function () {
        Branch::factory()->count(2)->create(['status' => 'active']);
        Branch::factory()->create(['status' => 'inactive']);

        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/admin/branches');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });

    test('super admin can create a branch', function () {
        $data = [
            'name' => 'Cabang Baru',
            'address' => 'Jl. Testing No.1',
            'phone' => '0221111111',
            'opening_time' => '08:00',
            'closing_time' => '22:00',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/branches', $data);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Cabang Baru');

        $this->assertDatabaseHas('branches', ['name' => 'Cabang Baru']);
    });

    test('super admin can update a branch', function () {
        $branch = Branch::factory()->create();

        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/admin/branches/{$branch->id}", [
                'name' => 'Cabang Updated',
                'status' => 'inactive',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Cabang Updated')
            ->assertJsonPath('data.status', 'inactive');
    });

    test('super admin can delete a branch (soft delete)', function () {
        $branch = Branch::factory()->create();

        $response = $this->actingAs($this->superAdmin)
            ->deleteJson("/api/admin/branches/{$branch->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('branches', ['id' => $branch->id]);
    });

    test('create branch validation fails with missing required fields', function () {
        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/branches', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'address']);
    });
});

// ==========================================
// Access Control
// ==========================================

describe('Branch Access Control', function () {
    test('admin cannot create a branch', function () {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/branches', [
                'name' => 'Test',
                'address' => 'Test Address',
            ]);

        $response->assertForbidden();
    });

    test('customer cannot create a branch', function () {
        $response = $this->actingAs($this->customer)
            ->postJson('/api/admin/branches', [
                'name' => 'Test',
                'address' => 'Test Address',
            ]);

        $response->assertForbidden();
    });

    test('unauthenticated user cannot access admin branch routes', function () {
        $response = $this->postJson('/api/admin/branches', [
            'name' => 'Test',
            'address' => 'Test Address',
        ]);

        $response->assertUnauthorized();
    });

    test('admin cannot delete a branch', function () {
        $branch = Branch::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/admin/branches/{$branch->id}");

        $response->assertForbidden();
    });
});
