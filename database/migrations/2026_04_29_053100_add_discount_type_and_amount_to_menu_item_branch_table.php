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
        Schema::table('menu_item_branch', function (Blueprint $table) {
            // Tipe diskon: 'percentage' atau 'fixed'. null = tidak ada promo.
            $table->enum('discount_type', ['percentage', 'fixed'])
                ->nullable()
                ->after('stock')
                ->comment('percentage = diskon persen, fixed = potongan langsung nominal');

            // Potongan langsung (nominal), e.g. Rp 5.000
            $table->decimal('discount_amount', 10, 2)
                ->nullable()
                ->after('discount_percentage')
                ->comment('Potongan langsung nominal, dipakai jika discount_type = fixed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_item_branch', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_amount']);
        });
    }
};
