<?php

namespace Database\Seeders;

use App\Models\Master;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $master = Master::create([
            'name' => "Default Master",
            'password' => Hash::make('password123'),
            'username' => 'defaultmaster123',
            'master_code' => 'MASTER',
            'phone_number' => '09000000000',
            'winning_commission_percentage' => 5,
            'is_default' => 1
        ]);
    }
}
