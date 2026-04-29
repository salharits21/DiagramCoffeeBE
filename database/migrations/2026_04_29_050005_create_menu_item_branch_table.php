<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menu_item_branch', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->boolean('is_available')->default(true);
            $table->integer('stock')->nullable()->comment('null = stok unlimited');
            $table->decimal('discount_percentage', 5, 2)->nullable()->comment('Persentase diskon promo, e.g. 10.00 = 10%');
            $table->boolean('is_promo_active')->default(false);
            $table->timestamps();

            // Satu menu hanya punya satu record per cabang
            $table->unique(['menu_item_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_item_branch');
    }
};
