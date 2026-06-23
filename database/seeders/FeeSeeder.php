<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppSetting;

class FeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fee_admin = AppSetting::create([
            'key' => 'admin_fee',
            'value' => '2000.00',
            'label' => 'Biaya Admin',
        ]);
    }
}