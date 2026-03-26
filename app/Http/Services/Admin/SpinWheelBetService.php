<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\SpinWheelBetRepository;
use Carbon\Carbon;
use Exception;

class SpinWheelBetService
{
    protected $spin_wheel_bet_repository;

    public function __construct(SpinWheelBetRepository $spin_wheel_bet_repository)
    {
        $this->spin_wheel_bet_repository = $spin_wheel_bet_repository;
    }

    public function getDataWithPagination(
        int $perPage = 10,
        int $page = 1,
        string $orderBy = 'created_at',
        array $searches = null,
        array $conditions = [],
        array $orConditions = [],
        array $with = [],
        ?array $whereHas = null,
        ?string $status = null
    ) {
        try {
            $res_data = $this->spin_wheel_bet_repository->getData(
                page: $page,
                perPage: $perPage,
                with: $with,
                whereHas: $whereHas
            );

            $res_data['data'] = collect($res_data['data'])->map(function ($bet) {

                $isWinner = (
                    $bet->round?->result &&
                    $bet->round->result->winner_user_id === $bet->user_id
                );

                return [
                    'id' => $bet->id,
                    'created_at' => Carbon::parse($bet->created_at)->format('Y-m-d H:i:s'),
                    'user_id' => $bet->user_id,
                    'user_name' => $bet->user?->name,
                    'game' => $bet->round?->gameRoom?->game?->name,
                    'bet_amount' => (float) $bet->bet_amount,
                    'winning_amount' => $isWinner
                        ? $bet->round->result->winner_payout
                        : 0,
                    'status' => $isWinner ? 'won' : 'lost',
                    'room' => $bet->round?->gameRoom?->room_name,
                    'round' => $bet->round?->round_number,
                ];
            });

            return $res_data;

        } catch (Exception $e) {
            logger()->error(
                'Error : Failed to fetch spin wheel bet history data with pagination: '
                . $e->getMessage()
            );
            throw $e;
        }
    }

    public function find(int $id)
    {
        try {
            $result = $this->spin_wheel_bet_repository->find($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch spin wheel bet: ' . $e->getMessage());
            throw $e;
        }
    }
}
