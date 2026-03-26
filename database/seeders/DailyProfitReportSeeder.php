<?php

namespace Database\Seeders;

use App\Models\DailyProfitReport;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DailyProfitReportSeeder extends Seeder
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
            $moneyTransfer = rand(300, 2000);

            DailyProfitReport::create([
                'report_date' => $date->format('Y-m-d'),
                'spin_wheel_profit' => $spinWheel,
                'coin_flip_profit' => $coinFlip,
                'money_transfer_profit' => $moneyTransfer,
                'total_profit' => $spinWheel + $coinFlip + $moneyTransfer
            ]);
        }
    }
}
