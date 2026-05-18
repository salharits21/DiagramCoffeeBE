<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Verifikasi email untuk semua super_admin & admin yang sudah ada.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereIn('role', ['super_admin', 'admin'])
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Tidak perlu rollback — data historis
    }
};
