<?php

use App\Models\User;
use App\Models\MenuItem;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
});

describe('Menu Item CSV Import', function () {
    test('super admin can import menu items via csv', function () {
        $csvContent = "name,description,base_price\n";
        $csvContent .= "Kopi Hitam,Kopi hitam manis,15000\n";
        $csvContent .= "Kopi Susu,,18000\n";
        
        $file = UploadedFile::fake()->createWithContent('menu.csv', $csvContent);

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/menu-items/import', [
                'file' => $file,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('imported_count', 2)
            ->assertJsonPath('failed_count', 0);

        $this->assertDatabaseHas('menu_items', [
            'name' => 'Kopi Hitam',
            'base_price' => '15000',
            'category_id' => null,
        ]);

        $this->assertDatabaseHas('menu_items', [
            'name' => 'Kopi Susu',
            'base_price' => '18000',
            'category_id' => null,
            'description' => null,
        ]);
    });

    test('import skips rows with missing name or price', function () {
        $csvContent = "name,description,base_price\n";
        $csvContent .= "Teh Manis,Teh,10000\n"; // Valid
        $csvContent .= ",Kosong name,10000\n"; // Invalid
        $csvContent .= "Teh Tawar,Teh,bukan_angka\n"; // Invalid
        
        $file = UploadedFile::fake()->createWithContent('menu2.csv', $csvContent);

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/menu-items/import', [
                'file' => $file,
            ]);

        $response->assertOk()
            ->assertJsonPath('imported_count', 1)
            ->assertJsonPath('failed_count', 2);
    });

    test('import handles duplicate slugs by appending unique string', function () {
        MenuItem::factory()->create(['name' => 'Es Teh', 'slug' => 'es-teh']);

        $csvContent = "name,description,base_price\n";
        $csvContent .= "Es Teh,Teh,10000\n";
        
        $file = UploadedFile::fake()->createWithContent('menu3.csv', $csvContent);

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/menu-items/import', [
                'file' => $file,
            ]);

        $response->assertOk()
            ->assertJsonPath('imported_count', 1);

        $menuItem = MenuItem::where('name', 'Es Teh')->latest('id')->first();
        expect($menuItem->slug)->not->toBe('es-teh');
        expect(str_starts_with($menuItem->slug, 'es-teh-'))->toBeTrue();
    });
});
