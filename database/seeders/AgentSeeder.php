<?php

namespace Database\Seeders;

use App\Models\Agent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agent = Agent::create([
            'name' => "Default Agent",
            'password' => Hash::make('password123'),
            'username' => 'defaultagent123',
            'phone_number' => '09000000000',
            'agent_code' => 'DEFAULT',
            'winning_commission_percentage' => 2,
            'master_id' => 1,
            'is_default' => 1
        ]);
    }
}
