<?php

namespace Database\Seeders;

use App\Models\DailyHouseCutReport;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DailyHouseCutReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $startDate = Carbon::today()->subDays(400);

        for ($i = 0; $i <= 400; $i++) {

            $date = $startDate->copy()->addDays($i);

            $spinWheel = rand(500, 3000);
            $coinFlip = rand(300, 2000);

            DailyHouseCutReport::create([
                'report_date' => $date->format('Y-m-d'),
                'spin_wheel_house_cut' => $spinWheel,
                'coin_flip_house_cut' => $coinFlip,
                'total_house_cut' => $spinWheel + $coinFlip
            ]);
        }
    }
}
