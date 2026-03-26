<?php

namespace Database\Seeders;

use App\Models\DailyMoneyTransferCommissionReport;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DailyMoneyTransferCommissionReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $startDate = Carbon::today()->subDays(400);

        for ($i = 0; $i <= 400; $i++) {

            $date = $startDate->copy()->addDays($i);

            $amount = rand(500, 3000);

            DailyMoneyTransferCommissionReport::create([
                'report_date' => $date->format('Y-m-d'),
                'total_commission_amount' => $amount
            ]);
        }
    }
}
