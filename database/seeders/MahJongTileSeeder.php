<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MahJongTileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiles = [];
        $types = ['dot', 'bamboo'];

        foreach ($types as $type) {
            for ($number = 1; $number <= 9; $number++) {
                for ($copy = 1; $copy <= 4; $copy++) {
                    $tiles[] = [
                        'type' => $type,
                        'number' => $number,
                        'copy_no' => $copy,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        DB::table('mah_jong_tiles')->insert($tiles);
    }
}
