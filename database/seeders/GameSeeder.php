<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Game::updateOrCreate(
            ['name' => 'Shan Koe Mee'],
            ['status' => 'inactive']
        );

        Game::updateOrCreate(
            ['name' => 'Mah Jong'],
            ['status' => 'inactive']
        );
    }
}
