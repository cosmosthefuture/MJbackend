<?php

namespace App\Http\Repositories\Agent;

use App\Http\Repositories\BaseRepo;
use App\Models\Agent;
use App\Models\AgentIncentive;
use App\Models\AgentIncentiveMonthlySummary;
use App\Models\AgentWalletDailySummary;
use App\Models\UserFcmToken;

class AgentRepository extends BaseRepo
{
    public function __construct(Agent $model)
    {
        parent::__construct($model);
    }

    public function generateAccessToken(Agent $agent)
    {
        $agent->tokens()->delete();
        return $agent->createToken($agent->username . '_AccessToken', [''], now()->addDays(2))->plainTextToken;
    }

    public function generateRefreshToken(Agent $agent)
    {
        return $agent->createToken($agent->username . '_RefreshToken', [''], now()->addWeek())->plainTextToken;
    }

    public function getIncentiveTransactions($page = 1, $per_page = 10, $agentId)
    {
        $query = AgentIncentive::with('user')
            ->where('agent_id', $agentId)
            ->orderByDesc('date_time');

        $totalCount = $query->count();

        $offset = ($page - 1) * $per_page;

        $results = $query
            ->skip($offset)
            ->take($per_page)
            ->get()
            ->map(function ($item) {
                return [
                    'date_time' => $item->date_time,
                    'user' => $item->user,
                    'deposit_amount' => $item->deposit_amount,
                    'incentive_percentage' => $item->incentive_percentage,
                    'incentive_amount' => $item->incentive_amount,
                ];
            });

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

    public function getDailyWalletSummary($page = 1, $per_page = 10, $agentId)
    {
        $offset = ($page - 1) * $per_page;

        $query = AgentWalletDailySummary::where('agent_id', $agentId)
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

    public function getMonthlyIncentiveSummary($page, $per_page, $agentId)
    {
        $offset = ($page - 1) * $per_page;

        $query = AgentIncentiveMonthlySummary::where('agent_id', $agentId)
            ->orderByDesc('month');

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

    public function storeFcmToken($agent, $token)
    {
        $result = UserFcmToken::updateOrCreate(
            [
                'token' => $token,
            ],
            [
                'recipient_type' => 'agent',
                'recipient_id' => $agent->id,
            ]
        );
        return $result;
    }
}
