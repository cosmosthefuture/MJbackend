<?php

namespace Database\Seeders;

use App\Models\MahJongGameRoom;
use App\Models\MahJongGameRule;
use App\Models\MahJongGameRuleFee;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GlobalDataForTestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            "phone_number" => "09111111111",
            "name" => "User One",
            "username" => "userone123",
            "email" => "userone@example.com",
            "agent_id" => 1,
            "master_id" => 1,
            "password" => Hash::make("password123"),
        ]);
        User::create([
            "phone_number" => "09222222222",
            "name" => "User Two",
            "username" => "usertwo123",
            "email" => "usertwo@example.com",
            "agent_id" => 1,
            "master_id" => 1,
            "password" => Hash::make("password123"),
        ]);
        User::create([
            "phone_number" => "09333333333",
            "name" => "User Three",
            "username" => "userthree123",
            "email" => "userthree@example.com",
            "agent_id" => 1,
            "master_id" => 1,
            "password" => Hash::make("password123"),
        ]);

        $rule_one = MahJongGameRule::create([
            'rule_name' => 'Rule One',
            'round_qty_per_match' => 4,
            'max_player' => 4,
            'bet_amount' => 5000,
            'game_id' => 1,
            'created_by' => 1
        ]);
        $fees_one = [
            [
                'fee_type' => 'room',
                'amount' => 100,
                'payer_type' => 'each_player',
            ],
            [
                'fee_type' => 'registration',
                'amount' => 50,
                'payer_type' => 'winner',
            ],
            [
                'fee_type' => 'winning_commission',
                'amount' => 5,
                'payer_type' => 'winner',
            ],
        ];
        foreach ($fees_one as $each) {
            MahJongGameRuleFee::create([
                'mah_jong_game_rule_id' => $rule_one->id,
                'fee_type' => $each['fee_type'],
                'amount' => $each['amount'],
                'payer_type' => $each['payer_type'],
            ]);
        }

        $rule_two = MahJongGameRule::create([
            'rule_name' => 'Rule Two',
            'round_qty_per_match' => 4,
            'max_player' => 4,
            'bet_amount' => 15000,
            'game_id' => 1,
            'created_by' => 1
        ]);

        $fees_two = [
            [
                'fee_type' => 'room',
                'amount' => 1000,
                'payer_type' => 'each_player',
            ],
            [
                'fee_type' => 'registration',
                'amount' => 500,
                'payer_type' => 'winner',
            ],
            [
                'fee_type' => 'winning_commission',
                'amount' => 50,
                'payer_type' => 'winner',
            ],
        ];
        foreach ($fees_two as $each) {
            MahJongGameRuleFee::create([
                'mah_jong_game_rule_id' => $rule_two->id,
                'fee_type' => $each['fee_type'],
                'amount' => $each['amount'],
                'payer_type' => $each['payer_type'],
            ]);
        }

        MahJongGameRoom::create([
            'room_name' => 'Room One',
            'room_code' => '0001',
            'game_id' => 1,
            'mah_jong_game_rule_id' => 1,
            'created_by' => 1
        ]);
        MahJongGameRoom::create([
            'room_name' => 'Room Two',
            'room_code' => '0002',
            'game_id' => 1,
            'mah_jong_game_rule_id' => 2,
            'created_by' => 1
        ]);
    }
}
