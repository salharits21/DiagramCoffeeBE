<?php

use App\Models\User;
use App\Models\Branch;

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
    $this->branch = Branch::factory()->create();
    $this->otherBranch = Branch::factory()->create();
    $this->admin = User::factory()->create([
        'role' => 'admin',
        'branch_id' => $this->branch->id,
    ]);
    $this->customer = User::factory()->create(['role' => 'customer']);
});

// ==========================================
// List & Show
// ==========================================

describe('List and Show Admins', function () {
    test('super admin can list all admins', function () {
        // $this->admin sudah ada dari beforeEach
        User::factory()->create([
            'role' => 'admin',
            'branch_id' => $this->otherBranch->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/admin/admins');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    });

    test('admin list only returns admin role users', function () {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/admin/admins');

        $response->assertOk();

        // Tidak menampilkan super_admin atau customer
        collect($response->json('data'))->each(function ($user) {
            expect($user['role'])->toBe('admin');
        });
    });

    test('admin list includes branch data', function () {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/admin/admins');

        $response->assertOk()
            ->assertJsonPath('data.0.branch.id', $this->branch->id);
    });

    test('super admin can view a single admin', function () {
        $response = $this->actingAs($this->superAdmin)
            ->getJson("/api/admin/admins/{$this->admin->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $this->admin->id)
            ->assertJsonPath('data.branch.id', $this->branch->id);
    });

    test('show returns 404 for non-admin user id', function () {
        $response = $this->actingAs($this->superAdmin)
            ->getJson("/api/admin/admins/{$this->customer->id}");

        $response->assertNotFound();
    });
});

// ==========================================
// Create
// ==========================================

describe('Create Admin', function () {
    test('super admin can create an admin', function () {
        $data = [
            'name' => 'New Admin',
            'email' => 'newadmin@diagramcoffee.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'branch_id' => $this->otherBranch->id,
        ];

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/admins', $data);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'New Admin')
            ->assertJsonPath('data.role', 'admin')
            ->assertJsonPath('data.branch.id', $this->otherBranch->id);

        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@diagramcoffee.com',
            'role' => 'admin',
            'branch_id' => $this->otherBranch->id,
        ]);
    });

    test('created admin always has role admin regardless of input', function () {
        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/admins', [
                'name' => 'Sneaky Admin',
                'email' => 'sneaky@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'branch_id' => $this->branch->id,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.role', 'admin');
    });

    test('cannot create admin without branch', function () {
        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/admins', [
                'name' => 'No Branch',
                'email' => 'nobranch@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['branch_id']);
    });

    test('cannot create admin with invalid branch', function () {
        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/admins', [
                'name' => 'Bad Branch',
                'email' => 'badbranch@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'branch_id' => 9999,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['branch_id']);
    });

    test('cannot create admin with duplicate email', function () {
        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/admins', [
                'name' => 'Duplicate',
                'email' => $this->admin->email,
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'branch_id' => $this->branch->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    test('cannot create admin with weak password', function () {
        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/admins', [
                'name' => 'Weak Pass',
                'email' => 'weak@test.com',
                'password' => '123',
                'password_confirmation' => '123',
                'branch_id' => $this->branch->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    });

    test('cannot create admin without password confirmation', function () {
        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/admins', [
                'name' => 'No Confirm',
                'email' => 'noconfirm@test.com',
                'password' => 'password123',
                'branch_id' => $this->branch->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    });
});

// ==========================================
// Update
// ==========================================

describe('Update Admin', function () {
    test('super admin can update admin name', function () {
        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/admin/admins/{$this->admin->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'name' => 'Updated Name',
        ]);
    });

    test('super admin can reassign admin to different branch', function () {
        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/admin/admins/{$this->admin->id}", [
                'branch_id' => $this->otherBranch->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.branch.id', $this->otherBranch->id);
    });

    test('super admin can update admin email', function () {
        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/admin/admins/{$this->admin->id}", [
                'email' => 'newemail@diagramcoffee.com',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.email', 'newemail@diagramcoffee.com');
    });

    test('super admin can update admin password', function () {
        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/admin/admins/{$this->admin->id}", [
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertOk();
    });

    test('cannot update non-admin user via admin endpoint', function () {
        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/admin/admins/{$this->customer->id}", [
                'name' => 'Hacked',
            ]);

        $response->assertNotFound();
    });

    test('update email unique validation excludes self', function () {
        // Admin should be able to "update" with their own email
        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/admin/admins/{$this->admin->id}", [
                'email' => $this->admin->email,
            ]);

        $response->assertOk();
    });
});

// ==========================================
// Delete
// ==========================================

describe('Delete Admin', function () {
    test('super admin can delete an admin', function () {
        $adminId = $this->admin->id;

        $response = $this->actingAs($this->superAdmin)
            ->deleteJson("/api/admin/admins/{$adminId}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('users', ['id' => $adminId]);
    });

    test('cannot delete non-admin user via admin endpoint', function () {
        $response = $this->actingAs($this->superAdmin)
            ->deleteJson("/api/admin/admins/{$this->customer->id}");

        $response->assertNotFound();
    });

    test('deleted admin tokens are revoked', function () {
        // Create a token for admin
        $this->admin->createToken('test_token');
        expect($this->admin->tokens()->count())->toBe(1);

        $this->actingAs($this->superAdmin)
            ->deleteJson("/api/admin/admins/{$this->admin->id}");

        expect(\Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $this->admin->id)->count())->toBe(0);
    });
});

// ==========================================
// Access Control
// ==========================================

describe('Admin Management Access Control', function () {
    test('admin cannot access admin management', function () {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/admins');

        $response->assertForbidden();
    });

    test('customer cannot access admin management', function () {
        $response = $this->actingAs($this->customer)
            ->getJson('/api/admin/admins');

        $response->assertForbidden();
    });

    test('unauthenticated user cannot access admin management', function () {
        $response = $this->getJson('/api/admin/admins');

        $response->assertUnauthorized();
    });

    test('admin cannot create another admin', function () {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/admins', [
                'name' => 'Test',
                'email' => 'test@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'branch_id' => $this->branch->id,
            ]);

        $response->assertForbidden();
    });

    test('admin cannot delete another admin', function () {
        $otherAdmin = User::factory()->create([
            'role' => 'admin',
            'branch_id' => $this->otherBranch->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/admin/admins/{$otherAdmin->id}");

        $response->assertForbidden();
    });
});
