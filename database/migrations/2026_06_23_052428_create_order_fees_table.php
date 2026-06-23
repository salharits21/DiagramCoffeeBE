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
        // Hapus kolom admin_fee lama
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('admin_fee');
        });

        // Buat tabel multi-fee
        Schema::create('order_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('fee_key');
            $table->string('fee_label');
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_fees');

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('admin_fee', 10, 2)->default(2000)->after('discount_total');
        });
    }
};
