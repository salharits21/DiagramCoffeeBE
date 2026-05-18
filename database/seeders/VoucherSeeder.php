<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Voucher::create([
            'name' => 'Diskon Rp 10.000 (Min. Transaksi Rp 50.000)',
            'code' => 'DISC10K',
            'discount_amount' => 10000,
            'min_transaction_amount' => 50000,
            'points_required' => 50, // Misal: 50 poin = 10rb
            'is_active' => true,
        ]);

        Voucher::create([
            'name' => 'Diskon Rp 25.000 (Min. Transaksi Rp 100.000)',
            'code' => 'DISC25K',
            'discount_amount' => 25000,
            'min_transaction_amount' => 100000,
            'points_required' => 100, // Misal: 100 poin = 25rb
            'is_active' => true,
        ]);
        
        Voucher::create([
            'name' => 'Diskon Rp 50.000 (Min. Transaksi Rp 150.000)',
            'code' => 'DISC50K',
            'discount_amount' => 50000,
            'min_transaction_amount' => 150000,
            'points_required' => 180, // Misal: Lebih hemat
            'is_active' => true,
        ]);
    }
}
