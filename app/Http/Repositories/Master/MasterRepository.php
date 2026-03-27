<?php

namespace App\Http\Repositories\Master;

use App\Http\Repositories\BaseRepo;
use App\Models\Agent;
use App\Models\AgentIncentive;
use App\Models\Master;
use App\Models\MasterIncentive;
use App\Models\MasterWalletDailySummary;
use App\Models\MasterWalletRecord;

class MasterRepository extends BaseRepo
{
    public function __construct(Master $model)
    {
        parent::__construct($model);
    }

    public function generateAccessToken(Master $master)
    {
        $master->tokens()->delete();
        return $master->createToken($master->username . '_AccessToken', [''], now()->addDays(2))->plainTextToken;
    }

    public function generateRefreshToken(Master $master)
    {
        return $master->createToken($master->username . '_RefreshToken', [''], now()->addWeek())->plainTextToken;
    }

    public function getDailyWalletSummary($page = 1, $per_page = 10, $masterId)
    {
        $offset = ($page - 1) * $per_page;

        $query = MasterWalletDailySummary::where('master_id', $masterId)
            ->orderByDesc('date');

        $totalCount = $query->count();

        $results = $query
            ->skip($offset)
            ->take($per_page)
            ->get();

        $totalPages = (int) ceil($totalCount / $per_page);

        return [
            'data' => $results,
            'meta' => [
                'total' => $totalCount,
                'per_page' => $per_page,
                'current_page' => $page,
                'total_pages' => $totalPages,
            ],
        ];
    }

    public function getMonthlyIncentiveReport($page, $per_page, $masterId)
    {
        $monthsQuery = MasterIncentive::where('master_id', $masterId)
            ->selectRaw("DISTINCT month")
            ->orderByDesc('month');

        $totalMonths = MasterIncentive::where('master_id', $masterId)
            ->pluck('month')
            ->unique()
            ->count();

        $months = $monthsQuery
            ->skip(($page - 1) * $per_page)
            ->take($per_page)
            ->pluck('month');

        $agents = Agent::where('master_id', $masterId)->get();

        $monthAgentPairs = collect();
        foreach ($months as $month) {
            foreach ($agents as $agent) {
                $monthAgentPairs->push([
                    'month' => $month,
                    'agent_id' => $agent->id,
                    'agent_name' => $agent->name,
                ]);
            }
        }

        $masterIncentives = MasterIncentive::where('master_id', $masterId)
            ->whereIn('month', $months)
            ->select('month', 'agent_id')
            ->selectRaw("
        SUM(deposit_amount) as total_deposit_amount,
        SUM(incentive_amount) as total_master_incentive_amount
    ")
            ->groupBy('month', 'agent_id')
            ->get()
            ->keyBy(fn($row) => $row->month . '-' . $row->agent_id);

        $agentIncentives = AgentIncentive::whereIn('month', $months)
            ->select('month', 'agent_id')
            ->selectRaw("SUM(incentive_amount) as total_agent_incentive_amount")
            ->groupBy('month', 'agent_id')
            ->get()
            ->keyBy(fn($row) => $row->month . '-' . $row->agent_id);

        $monthlyTotals = MasterIncentive::where('master_id', $masterId)
            ->whereIn('month', $months)
            ->select('month')
            ->selectRaw("
        SUM(deposit_amount) as month_total_deposit_amount,
        SUM(incentive_amount) as month_total_master_incentive_amount
    ")
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $monthlyAgentTotals = AgentIncentive::whereIn('month', $months)
            ->select('month')
            ->selectRaw("
        SUM(incentive_amount) as month_total_agent_incentive_amount
    ")
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $result = [];
        foreach ($months as $month) {
            $agentsData = $agents->map(function ($agent) use ($month, $masterIncentives, $agentIncentives) {
                $key = $month . '-' . $agent->id;

                return [
                    'agent_id' => $agent->id,
                    'agent_name' => $agent->name,
                    'total_deposit_amount' => (float) optional($masterIncentives->get($key))->total_deposit_amount ?? 0,
                    'total_agent_incentive_amount' => (float) optional($agentIncentives->get($key))->total_agent_incentive_amount ?? 0,
                    'total_master_incentive_amount' => (float) optional($masterIncentives->get($key))->total_master_incentive_amount ?? 0,
                ];
            });

            $result[] = [
                'month' => $month,
                'agents' => $agentsData->values(),
                'month_total' => [
                    'total_deposit_amount' => (float) optional($monthlyTotals->get($month))->month_total_deposit_amount ?? 0,
                    'total_agent_incentive_amount' => (float) optional($monthlyAgentTotals->get($month))->month_total_agent_incentive_amount ?? 0,
                    'total_master_incentive_amount' => (float) optional($monthlyTotals->get($month))->month_total_master_incentive_amount ?? 0,
                ],
            ];
        }

        $response = [
            'data' => $result,
            'meta' => [
                'total' => $totalMonths,
                'per_page' => $per_page,
                'current_page' => $page,
                'total_pages' => ceil($totalMonths / $per_page),
            ],
        ];
        return $response;
    }

    public function getMasterWalletRecords($page = 1, $per_page = 10, $masterId)
    {
        $offset = ($page - 1) * $per_page;

        $query = MasterWalletRecord::where('master_id', $masterId)
            ->orderBy('date_time', 'asc');

        $totalCount = $query->count();

        $results = $query
            ->skip($offset)
            ->take($per_page)
            ->get();

        $totalPages = (int) ceil($totalCount / $per_page);

        return [
            'data' => $results,
            'meta' => [
                'total' => $totalCount,
                'per_page' => $per_page,
                'current_page' => $page,
                'total_pages' => $totalPages,
            ],
        ];
    }
}
