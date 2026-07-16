<?php

use App\Models\User;
use App\Models\MenuItem;
use App\Models\Category;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
});

describe('Menu Item CSV Import', function () {
    test('super admin can import menu items via csv with categories', function () {
        $category = Category::factory()->create(['name' => 'Minuman']);
        
        $csvContent = "category,name,description,base_price\n";
        $csvContent .= "Minuman,Kopi Hitam,Kopi hitam manis,15000\n";
        $csvContent .= "Minuman,Kopi Susu,,18000\n";
        
        $file = UploadedFile::fake()->createWithContent('menu.csv', $csvContent);

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/menu-items/import', [
                'file' => $file,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('imported_count', 2);

        $this->assertDatabaseHas('menu_items', [
            'name' => 'Kopi Hitam',
            'base_price' => '15000',
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('menu_items', [
            'name' => 'Kopi Susu',
            'base_price' => '18000',
            'category_id' => $category->id,
            'description' => null,
        ]);
    });

    test('import fails if category column is positioned after name', function () {
        $csvContent = "name,category,description,base_price\n"; // Category is after name
        $csvContent .= "Kopi Hitam,Minuman,Kopi hitam manis,15000\n";
        
        $file = UploadedFile::fake()->createWithContent('menu_order.csv', $csvContent);

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/menu-items/import', [
                'file' => $file,
            ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Kolom category harus berada sebelum kolom name');
    });

    test('import fails with detailed errors on invalid data', function () {
        Category::factory()->create(['name' => 'Minuman']);
        MenuItem::factory()->create(['name' => 'Teh Manis']);

        $csvContent = "category,name,description,base_price\n";
        $csvContent .= "Makanan,Roti Bakar,Enak,10000\n"; // Category not found
        $csvContent .= "Minuman,Teh Manis,,10000\n"; // Duplicate in DB
        $csvContent .= "Minuman,Jus Apel,,15000\n"; // Valid but aborted due to other errors
        $csvContent .= "Minuman,Jus Apel,,15000\n"; // Duplicate in CSV
        $csvContent .= "Minuman,Kosong Harga,,bukan_angka\n"; // Invalid price
        
        $file = UploadedFile::fake()->createWithContent('menu_invalid.csv', $csvContent);

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/admin/menu-items/import', [
                'file' => $file,
            ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Terdapat kesalahan validasi pada file CSV.')
            ->assertJsonStructure(['errors']);

        $errors = $response->json('errors');
        expect($errors)->toHaveCount(4); // Category empty/not found, Name DB Duplicate, Name CSV Duplicate, Price Invalid.
        expect($errors[0])->toContain("Baris 2: Kategori 'Makanan' tidak ditemukan");
        expect($errors[1])->toContain("Baris 3: Menu 'Teh Manis' sudah ada di sistem");
        expect($errors[2])->toContain("Baris 5: Menu 'Jus Apel' duplikat di dalam file CSV");
        expect($errors[3])->toContain("Baris 6: Harga dasar 'bukan_angka' tidak valid");
        
        // Ensure no data was imported due to abort
        $this->assertDatabaseMissing('menu_items', [
            'name' => 'Jus Apel', 
        ]);
    });
});

describe('Menu Item CSV Export', function () {
    test('super admin can export all menu items to csv', function () {
        $category = Category::factory()->create(['name' => 'Minuman']);
        
        MenuItem::factory()->create([
            'category_id' => $category->id,
            'name' => 'Kopi Hitam',
            'description' => 'Kopi pahit',
            'base_price' => 15000,
        ]);

        MenuItem::factory()->create([
            'category_id' => null, // No category
            'name' => 'Air Mineral',
            'description' => null,
            'base_price' => 5000,
            'is_active' => false, // Ensure inactive is exported too
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get('/api/admin/menu-items/export');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        
        // Read the streamed response content
        $content = $response->streamedContent();
        
        // Assert header is present
        expect($content)->toContain("category,name,description,base_price\n");
        // Assert data is present
        expect($content)->toContain("Minuman,\"Kopi Hitam\",\"Kopi pahit\",15000.00\n");
        expect($content)->toContain(",\"Air Mineral\",,5000.00\n");
    });
});
