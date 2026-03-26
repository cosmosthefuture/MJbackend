<?php

namespace Database\Seeders;

use App\Models\GlobalCommissionSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GlobalCommissionSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GlobalCommissionSetting::create([
            'name' => 'Shan Koe Mee House Cut Percentage',
            'value' => 5
        ]);

        GlobalCommissionSetting::create([
            'name' => 'Mah Jong House Cut Percentage',
            'value' => 5
        ]);

        GlobalCommissionSetting::create([
            'name' => 'User Money Transfer House Cut Percentage',
            'value' => 1
        ]);
    }
}
