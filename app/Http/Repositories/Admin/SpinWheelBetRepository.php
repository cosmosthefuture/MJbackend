<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;
use App\Models\SpinWheelBet;
use DB;


class SpinWheelBetRepository extends BaseRepo
{
    public function __construct(SpinWheelBet $model)
    {
        parent::__construct($model);
    }

    public function find($id)
    {
        $data = SpinWheelBet::find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function getData(
        int $page = 1,
        int $perPage = 10,
        array $with = [],
        ?array $whereHas = null
    ) {
        $baseQuery = $this->model
            ->selectRaw('
            spin_wheel_bets.user_id,
            spin_wheel_bets.spin_wheel_round_id,
            MIN(spin_wheel_bets.id) as id,
            SUM(spin_wheel_bets.bet_amount) as bet_amount,
            MAX(spin_wheel_bets.created_at) as created_at
        ')
            ->groupBy(
                'spin_wheel_bets.user_id',
                'spin_wheel_bets.spin_wheel_round_id'
            )
            ->orderByDesc(DB::raw('MAX(spin_wheel_bets.created_at)'));

        if ($whereHas) {
            foreach ($whereHas as $relation => $callback) {
                $baseQuery->whereHas($relation, $callback);
            }
        }

        $totalCount = DB::query()
            ->fromSub($baseQuery, 'grouped_bets')
            ->count();

        $results = $baseQuery
            ->with($with)
            ->forPage($page, $perPage)
            ->get();

        $totalPages = (int) ceil($totalCount / $perPage);

        return [
            'data' => $results,
            'meta' => [
                'total' => $totalCount,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages,
            ],
        ];
    }
}
