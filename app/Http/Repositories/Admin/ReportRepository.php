<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;
use App\Models\DailyDepositReport;
use App\Models\DailyHouseCutReport;
use App\Models\DailyMoneyTransferCommissionReport;
use App\Models\DailyProfitReport;
use App\Models\Game;
use App\Models\UserGameHistory;
use Carbon\Carbon;
use DB;


class ReportRepository
{
    public function __construct()
    {
        // 
    }

    public function getDailyHouseCutReports()
    {
        $start = Carbon::today()->subDays(11);
        $end = Carbon::today();

        $reports = DailyHouseCutReport::whereBetween('report_date', [$start, $end])
            ->pluck('total_house_cut', 'report_date');

        $categories = [];
        $data = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {

            $key = $date->format('Y-m-d');

            $categories[] = $key;
            $data[] = (float) ($reports[$key] ?? 0);
        }

        return [
            "categories" => $categories,
            "series" => [
                [
                    "name" => "Daily Commission Amount",
                    "data" => $data
                ]
            ]
        ];
    }

    public function getMonthlyHouseCutReports()
    {
        $start = now()->startOfMonth()->subMonths(11);

        $reports = DailyHouseCutReport::selectRaw("
            YEAR(report_date) as year,
            MONTH(report_date) as month,
            SUM(total_house_cut) as total
        ")
            ->where('report_date', '>=', $start)
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(function ($item) {
                return $item->year . '-' . $item->month;
            });

        $categories = [];
        $data = [];

        for ($i = 0; $i < 12; $i++) {

            $date = $start->copy()->addMonths($i);

            $key = $date->year . '-' . $date->month;

            $categories[] = $date->format('M');
            $data[] = (float) ($reports[$key]->total ?? 0);
        }

        return [
            "categories" => $categories,
            "series" => [
                [
                    "name" => "Monthly Commission Amount",
                    "data" => $data
                ]
            ]
        ];
    }

    public function getQuarterlyHouseCutReports()
    {
        $start = now()->startOfQuarter()->subQuarters(11);

        $reports = DailyHouseCutReport::selectRaw("
            YEAR(report_date) as year,
            QUARTER(report_date) as quarter,
            SUM(total_house_cut) as total
        ")
            ->where('report_date', '>=', $start)
            ->groupBy('year', 'quarter')
            ->get()
            ->keyBy(function ($item) {
                return $item->year . '-Q' . $item->quarter;
            });

        $categories = [];
        $data = [];

        for ($i = 0; $i < 12; $i++) {

            $date = $start->copy()->addQuarters($i);

            $quarter = ceil($date->month / 3);
            $key = $date->year . '-Q' . $quarter;

            $categories[] = $date->year . " Q" . $quarter;
            $data[] = (float) ($reports[$key]->total ?? 0);
        }

        return [
            "categories" => $categories,
            "series" => [
                [
                    "name" => "Quarterly Commission Amount",
                    "data" => $data
                ]
            ]
        ];
    }

    public function getDailyHouseCutReportsWithPagination(
        $perPage = 20,
        $page = 1,
        $startDate = null,
        $endDate = null
    ) {
        $query = DailyHouseCutReport::query();

        if ($startDate && $endDate) {
            $query->whereBetween('report_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('report_date', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('report_date', '<=', $endDate);
        }

        $query->orderByDesc('report_date');

        $total = $query->count();

        $data = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage),
            ],
        ];
    }

    public function getDailyDepositReports()
    {
        $start = Carbon::today()->subDays(11);
        $end = Carbon::today();

        $reports = DailyDepositReport::whereBetween('report_date', [$start, $end])
            ->pluck('total_deposit', 'report_date');

        $categories = [];
        $data = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {

            $key = $date->format('Y-m-d');

            $categories[] = $key;
            $data[] = (float) ($reports[$key] ?? 0);
        }

        return [
            "categories" => $categories,
            "series" => [
                [
                    "name" => "Daily Deposit Amount",
                    "data" => $data
                ]
            ]
        ];
    }

    public function getMonthlyDepositReports()
    {
        $start = now()->startOfMonth()->subMonths(11);

        $reports = DailyDepositReport::selectRaw("
            YEAR(report_date) as year,
            MONTH(report_date) as month,
            SUM(total_deposit) as total
        ")
            ->where('report_date', '>=', $start)
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(function ($item) {
                return $item->year . '-' . $item->month;
            });

        $categories = [];
        $data = [];

        for ($i = 0; $i < 12; $i++) {

            $date = $start->copy()->addMonths($i);

            $key = $date->year . '-' . $date->month;

            $categories[] = $date->format('M');
            $data[] = (float) ($reports[$key]->total ?? 0);
        }

        return [
            "categories" => $categories,
            "series" => [
                [
                    "name" => "Monthly Deposit Amount",
                    "data" => $data
                ]
            ]
        ];
    }

    public function getQuarterlyDepositReports()
    {
        $start = now()->startOfQuarter()->subQuarters(11);

        $reports = DailyDepositReport::selectRaw("
            YEAR(report_date) as year,
            QUARTER(report_date) as quarter,
            SUM(total_deposit) as total
        ")
            ->where('report_date', '>=', $start)
            ->groupBy('year', 'quarter')
            ->get()
            ->keyBy(function ($item) {
                return $item->year . '-Q' . $item->quarter;
            });

        $categories = [];
        $data = [];

        for ($i = 0; $i < 12; $i++) {

            $date = $start->copy()->addQuarters($i);

            $quarter = ceil($date->month / 3);
            $key = $date->year . '-Q' . $quarter;

            $categories[] = $date->year . " Q" . $quarter;
            $data[] = (float) ($reports[$key]->total ?? 0);
        }

        return [
            "categories" => $categories,
            "series" => [
                [
                    "name" => "Quarterly Deposit Amount",
                    "data" => $data
                ]
            ]
        ];
    }

    public function getDailyDepositReportsWithPagination(
        $perPage = 20,
        $page = 1,
        $startDate = null,
        $endDate = null
    ) {
        $query = DailyDepositReport::query();

        if ($startDate && $endDate) {
            $query->whereBetween('report_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('report_date', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('report_date', '<=', $endDate);
        }

        $query->orderByDesc('report_date');

        $total = $query->count();

        $data = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage),
            ],
        ];
    }

    public function getDailyMoneyTransferReports()
    {
        $start = Carbon::today()->subDays(11);
        $end = Carbon::today();

        $reports = DailyMoneyTransferCommissionReport::whereBetween('report_date', [$start, $end])
            ->pluck('total_commission_amount', 'report_date');

        $categories = [];
        $data = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {

            $key = $date->format('Y-m-d');

            $categories[] = $key;
            $data[] = (float) ($reports[$key] ?? 0);
        }

        return [
            "categories" => $categories,
            "series" => [
                [
                    "name" => "Daily Money Transfer Commission Amount",
                    "data" => $data
                ]
            ]
        ];
    }

    public function getMonthlyMoneyTransferReports()
    {
        $start = now()->startOfMonth()->subMonths(11);

        $reports = DailyMoneyTransferCommissionReport::selectRaw("
            YEAR(report_date) as year,
            MONTH(report_date) as month,
            SUM(total_commission_amount) as total
        ")
            ->where('report_date', '>=', $start)
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(function ($item) {
                return $item->year . '-' . $item->month;
            });

        $categories = [];
        $data = [];

        for ($i = 0; $i < 12; $i++) {

            $date = $start->copy()->addMonths($i);

            $key = $date->year . '-' . $date->month;

            $categories[] = $date->format('M');
            $data[] = (float) ($reports[$key]->total ?? 0);
        }

        return [
            "categories" => $categories,
            "series" => [
                [
                    "name" => "Monthly Money Transfer Commission Amount",
                    "data" => $data
                ]
            ]
        ];
    }

    public function getQuarterlyMoneyTransferReports()
    {
        $start = now()->startOfQuarter()->subQuarters(11);

        $reports = DailyMoneyTransferCommissionReport::selectRaw("
            YEAR(report_date) as year,
            QUARTER(report_date) as quarter,
            SUM(total_commission_amount) as total
        ")
            ->where('report_date', '>=', $start)
            ->groupBy('year', 'quarter')
            ->get()
            ->keyBy(function ($item) {
                return $item->year . '-Q' . $item->quarter;
            });

        $categories = [];
        $data = [];

        for ($i = 0; $i < 12; $i++) {

            $date = $start->copy()->addQuarters($i);

            $quarter = ceil($date->month / 3);
            $key = $date->year . '-Q' . $quarter;

            $categories[] = $date->year . " Q" . $quarter;
            $data[] = (float) ($reports[$key]->total ?? 0);
        }

        return [
            "categories" => $categories,
            "series" => [
                [
                    "name" => "Quarterly Money Transfer Commission Amount",
                    "data" => $data
                ]
            ]
        ];
    }

    public function getDailyMoneyTransferReportsWithPagination(
        $perPage = 20,
        $page = 1,
        $startDate = null,
        $endDate = null
    ) {
        $query = DailyMoneyTransferCommissionReport::query();

        if ($startDate && $endDate) {
            $query->whereBetween('report_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('report_date', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('report_date', '<=', $endDate);
        }

        $query->orderByDesc('report_date');

        $total = $query->count();

        $data = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage),
            ],
        ];
    }

    public function getDailyProfitReports()
    {
        $start = Carbon::today()->subDays(11);
        $end = Carbon::today();

        $reports = DailyProfitReport::whereBetween('report_date', [$start, $end])
            ->pluck('total_profit', 'report_date');

        $categories = [];
        $data = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {

            $key = $date->format('Y-m-d');

            $categories[] = $key;
            $data[] = (float) ($reports[$key] ?? 0);
        }

        return [
            "categories" => $categories,
            "series" => [
                [
                    "name" => "Daily Profit Amount",
                    "data" => $data
                ]
            ]
        ];
    }

    public function getMonthlyProfitReports()
    {
        $start = now()->startOfMonth()->subMonths(11);

        $reports = DailyProfitReport::selectRaw("
            YEAR(report_date) as year,
            MONTH(report_date) as month,
            SUM(total_profit) as total
        ")
            ->where('report_date', '>=', $start)
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(function ($item) {
                return $item->year . '-' . $item->month;
            });

        $categories = [];
        $data = [];

        for ($i = 0; $i < 12; $i++) {

            $date = $start->copy()->addMonths($i);

            $key = $date->year . '-' . $date->month;

            $categories[] = $date->format('M');
            $data[] = (float) ($reports[$key]->total ?? 0);
        }

        return [
            "categories" => $categories,
            "series" => [
                [
                    "name" => "Monthly Profit Amount",
                    "data" => $data
                ]
            ]
        ];
    }

    public function getQuarterlyProfitReports()
    {
        $start = now()->startOfQuarter()->subQuarters(11);

        $reports = DailyProfitReport::selectRaw("
            YEAR(report_date) as year,
            QUARTER(report_date) as quarter,
            SUM(total_profit) as total
        ")
            ->where('report_date', '>=', $start)
            ->groupBy('year', 'quarter')
            ->get()
            ->keyBy(function ($item) {
                return $item->year . '-Q' . $item->quarter;
            });

        $categories = [];
        $data = [];

        for ($i = 0; $i < 12; $i++) {

            $date = $start->copy()->addQuarters($i);

            $quarter = ceil($date->month / 3);
            $key = $date->year . '-Q' . $quarter;

            $categories[] = $date->year . " Q" . $quarter;
            $data[] = (float) ($reports[$key]->total ?? 0);
        }

        return [
            "categories" => $categories,
            "series" => [
                [
                    "name" => "Quarterly Profit Amount",
                    "data" => $data
                ]
            ]
        ];
    }

    public function getDailyProfitReportsWithPagination(
        $perPage = 20,
        $page = 1,
        $startDate = null,
        $endDate = null
    ) {
        $query = DailyProfitReport::query();

        if ($startDate && $endDate) {
            $query->whereBetween('report_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('report_date', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('report_date', '<=', $endDate);
        }

        $query->orderByDesc('report_date');

        $total = $query->count();

        $data = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage),
            ],
        ];
    }

    public function getUserGameHistoryWithPagination(
        int $userId,
        int $perPage = 20,
        int $page = 1
    ) {
        $query = UserGameHistory::where('user_id', $userId);

        $totalBetAmount = $query->sum('bet_amount');
        $totalWinningAmount = $query->sum('win_amount');

        $total = $query->count();

        $data = $query
            ->orderByDesc('created_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(function ($history) {
                return [
                    'id' => $history->id,
                    'game_type' => $history->game_type,
                    'bet_amount' => $history->bet_amount,
                    'winning_amount' => $history->win_amount,
                    'status' => $history->status,
                    'room' => $history->room_name,
                    'round' => $history->round_number,
                    'created_at' => $history->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return [
            'data' => [
                'total_bet_amount' => $totalBetAmount,
                'total_winning_amount' => $totalWinningAmount,
                'histories' => $data,
            ],
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage),
            ],
        ];
    }

    public function getMasterCommissionByMonth(
        int $perPage = 20,
        int $page = 1,
        string $month
    ) {

        $date = Carbon::createFromFormat('Y-m', $month);
        $monthNumber = $date->month;
        $yearNumber = $date->year;

        $query = DB::table('masters')
            ->leftJoin('daily_master_deposit_commission_reports as reports', function ($join) use ($monthNumber, $yearNumber) {
                $join->on('masters.id', '=', 'reports.master_id')
                    ->whereMonth('reports.report_date', $monthNumber)
                    ->whereYear('reports.report_date', $yearNumber);
            })
            ->select(
                'masters.id',
                'masters.name',
                DB::raw('COALESCE(SUM(reports.deposit_commission_amount),0) as total_commission')
            )
            ->groupBy('masters.id', 'masters.name')
            ->orderByDesc('total_commission');

        $total = DB::table('masters')->count();

        $data = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage),
            ],
        ];
    }

    public function getAgentCommissionByMonth(
        int $perPage = 20,
        int $page = 1,
        string $month
    ) {

        $date = Carbon::createFromFormat('Y-m', $month);
        $monthNumber = $date->month;
        $yearNumber = $date->year;

        $query = DB::table('agents')
            ->leftJoin('daily_agent_deposit_commission_reports as reports', function ($join) use ($monthNumber, $yearNumber) {
                $join->on('agents.id', '=', 'reports.agent_id')
                    ->whereMonth('reports.report_date', $monthNumber)
                    ->whereYear('reports.report_date', $yearNumber);
            })
            ->select(
                'agents.id',
                'agents.name',
                DB::raw('COALESCE(SUM(reports.deposit_commission_amount),0) as total_commission')
            )
            ->groupBy('agents.id', 'agents.name')
            ->orderByDesc('total_commission');

        $total = DB::table('agents')->count();

        $data = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage),
            ],
        ];
    }
}
