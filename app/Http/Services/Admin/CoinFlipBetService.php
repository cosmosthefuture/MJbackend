<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\CoinFlipBetRepository;
use Exception;

class CoinFlipBetService
{
    protected $coin_flip_bet_repository;

    public function __construct(CoinFlipBetRepository $coin_flip_bet_repository)
    {
        $this->coin_flip_bet_repository = $coin_flip_bet_repository;
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
        ?string $status = null)
    {
        try {
            $res_data = $this->coin_flip_bet_repository->getDataWithPagination(page: $page, perPage: $perPage, with: $with, whereHas: $whereHas);
            $res_data['data'] = $res_data['data']->map(function ($bet) {
                $payout = $bet->round?->payouts
                        ?->firstWhere('user_id', $bet->user_id);
                return [
                    'id' => $bet->id,
                    'created_at' => $bet->created_at->format('Y-m-d H:i:s'),
                    'user_id' => $bet->user_id,
                    'user_name' => $bet->user?->name,
                    'game' => $bet->round?->gameRoom?->game?->name,
                    'bet_side' => $bet->side,
                    'bet_amount' => $bet->bet_amount,
                    'winning_amount' => $payout ? $payout->win_amount : 0,
                    'status' => (
                        $bet->round?->result &&
                        $bet->round->result->result_side === $bet->side
                    )
                        ? 'won'
                        : 'lost',
                    'room' => $bet->round?->gameRoom?->room_name,
                    'round' => $bet->round?->round_number,
                ];
            });

            return $res_data;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch coin flip bet history data with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function find(int $id)
    {
        try {
            $result = $this->coin_flip_bet_repository->find($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch spin wheel bet: ' . $e->getMessage());
            throw $e;
        }
    }
}
