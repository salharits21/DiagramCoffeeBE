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
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('order_type', ['dine_in', 'take_away'])->default('dine_in')->after('branch_id');
            $table->string('table_number')->nullable()->after('order_type');
            $table->decimal('admin_fee', 10, 2)->default(2000)->after('discount_total');
            $table->foreignId('voucher_id')->nullable()->after('admin_fee')->constrained('vouchers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['voucher_id']);
            $table->dropColumn(['order_type', 'table_number', 'admin_fee', 'voucher_id']);
        });
    }
};
