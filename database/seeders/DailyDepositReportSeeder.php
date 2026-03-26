<?php

namespace Database\Seeders;

use App\Models\DailyDepositReport;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DailyDepositReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $startDate = Carbon::today()->subDays(400);

        for ($i = 0; $i <= 400; $i++) {

            $date = $startDate->copy()->addDays($i);

            $requestDeposit = rand(500, 3000);
            $manualDeposit = rand(300, 2000);

            DailyDepositReport::create([
                'report_date' => $date->format('Y-m-d'),
                'request_deposit' => $requestDeposit,
                'manual_deposit' => $manualDeposit,
                'total_deposit' => $requestDeposit + $manualDeposit
            ]);
        }
    }
}
