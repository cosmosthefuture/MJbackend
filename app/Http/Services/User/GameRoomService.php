<?php

namespace App\Http\Services\User;

use App\Http\Repositories\User\GameRoomRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class GameRoomService
{
    protected $game_room_repository;

    public function __construct(GameRoomRepository $game_room_repository)
    {
        $this->game_room_repository = $game_room_repository;
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
            $result = $this->game_room_repository->getDataWithPagination(page: $page, perPage: $perPage, with: $with, status: $status, searches: $searches, conditions: $conditions);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch game room data with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getAll()
    {
        try {
            $result = $this->game_room_repository->allRooms();
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch game rooms: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getDataWithPaginationCached(
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
            $result = $this->game_room_repository->getDataWithPaginationCached(page: $page, perPage: $perPage, with: $with, status: $status, searches: $searches, conditions: $conditions);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch game room data with pagination cached: ' . $e->getMessage());

            throw $e;
        }
    }

    public function find(int $id)
    {
        try {
            $result = $this->game_room_repository->find($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch game room: ' . $e->getMessage());
            throw $e;
        }
    }

    public function whereFirst($column, $value)
    {
        try {
            $result = $this->game_room_repository->whereFirst($column, $value);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find game room with whereFirst: ' . $e->getMessage());
            throw $e;
        }
    }

    public function checkIfActiveRoundExistOrNotForSpinWheel($roomId)
    {
        try {
            $result = $this->game_room_repository->fetchLatestRound($roomId, 'spin_wheel');
            if (!$result) {
                return ['exist' => false];
            }
            $status = $result->status;
            if ($status == 'finished') {
                return ['exist' => false];
            }
            return ['exist' => true];
        } catch (Exception $e) {
            logger()->error('Error : Failed to check if active round exist or not for spin wheel: ' . $e->getMessage());
            throw $e;
        }
    }

    public function checkIfActiveRoundExistOrNotForCoinFlip($roomId)
    {
        try {
            $result = $this->game_room_repository->fetchLatestRound($roomId, 'coin_flip');
            if (!$result) {
                return ['exist' => false];
            }
            $status = $result->status;
            if ($status == 'finished') {
                return ['exist' => false];
            }
            return ['exist' => true];
        } catch (Exception $e) {
            logger()->error('Error : Failed to check if active round exist or not for coin flip: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createNewRoundForSpinWheel($roomId)
    {
        try {
            $result = $this->game_room_repository->createRoundForSpinWheel($roomId);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to create new round for spin wheel: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createNewRoundForCoinFlip($roomId)
    {
        try {
            $result = $this->game_room_repository->createRoundForCoinFlip($roomId);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to create new round for coin flip: ' . $e->getMessage());
            throw $e;
        }
    }

    public function fetchSpinWheelGameRound($column, $value)
    {
        try {
            $result = $this->game_room_repository->get_spin_wheel_game_round($column, $value);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find spin wheel game round: ' . $e->getMessage());
            throw $e;
        }
    }

    public function fetchCoinFlipGameRound($column, $value)
    {
        try {
            $result = $this->game_room_repository->get_coin_flip_game_round($column, $value);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find coin flip game round: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createBetForSpinWheel($data, $already_bet)
    {
        DB::beginTransaction();
        try {
            $result = $this->game_room_repository->create_spin_wheel_bet($data, $already_bet);
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to create spin wheel bet: ' . $e->getMessage());
            throw $e;
        }
    }

    public function cancelBetForSpinWheel($game_round)
    {
        DB::beginTransaction();
        try {
            $this->game_room_repository->cancel_spin_wheel_bet($game_round);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to cancel spin wheel bet: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createBetForCoinFlip($data, $already_bet)
    {
        DB::beginTransaction();
        try {
            $result = $this->game_room_repository->create_coin_flip_bet($data, $already_bet);
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to create coin flip bet: ' . $e->getMessage());
            throw $e;
        }
    }

    public function cancelBetForCoinFlip($game_round)
    {
        DB::beginTransaction();
        try {
            $this->game_room_repository->cancel_coin_flip_bet($game_round);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to cancel coin flip bet: ' . $e->getMessage());
            throw $e;
        }
    }

    public function calculateBetInfoForSpinWheel($round_id)
    {
        DB::beginTransaction();
        try {
            $result = $this->game_room_repository->calculateSpinWheelBetInfo($round_id);
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to calculate spin wheel bet info: ' . $e->getMessage());
            throw $e;
        }
    }

    public function calculateBetInfoForCoinFlip($round_id)
    {
        DB::beginTransaction();
        try {
            $result = $this->game_room_repository->calculateCoinFlipBetInfo($round_id);
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to calculate coin flip bet info: ' . $e->getMessage());
            throw $e;
        }
    }

    public function checkIfAlreadyBetOrNotForSpinWheel($round_id, $user_id)
    {
        try {
            $result = $this->game_room_repository->checkIfAlreadyBetOrNotForSpinWheel($round_id, $user_id);
            // if (!$result) {
            //     return false;
            // }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to check if already bet or not for spin wheel: ' . $e->getMessage());
            throw $e;
        }
    }

    public function checkIfAlreadyBetOrNotForCoinFlip($round_id, $user_id)
    {
        try {
            $result = $this->game_room_repository->checkIfAlreadyBetOrNotForCoinFlip($round_id, $user_id);
            if (!$result) {
                return false;
            }
            return true;
        } catch (Exception $e) {
            logger()->error('Error : Failed to check if already bet or not for coin flip: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getBetSide($round_id, $user_id)
    {
        try {
            $result = $this->game_room_repository->get_bet_side($round_id, $user_id);

            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to check if already bet or not for coin flip: ' . $e->getMessage());
            throw $e;
        }
    }

    public function calculateResultForSpinWheel($game_round)
    {
        DB::beginTransaction();
        try {
            $result = $this->game_room_repository->calculate_result_for_spin_wheel($game_round);
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to calculate result for spin wheel: ' . $e->getMessage());
            throw $e;
        }
    }

    public function calculateResultForCoinFlip($game_round)
    {
        DB::beginTransaction();
        try {
            $result = $this->game_room_repository->calculate_result_for_coin_flip($game_round);
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to calculate result for coin flip: ' . $e->getMessage());
            throw $e;
        }
    }

    public function finishGameRoundForSpinWheel($game_round)
    {
        DB::beginTransaction();
        try {
            $this->game_room_repository->finish_round_for_spin_wheel($game_round);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to finish round for spin wheel: ' . $e->getMessage());
            throw $e;
        }
    }

    public function finishGameRoundForCoinFlip($game_round)
    {
        DB::beginTransaction();
        try {
            $this->game_room_repository->finish_round_for_coin_flip($game_round);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to finish round for coin flip: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getTotalBetAmountOfTargetUserForSpinWheel($roundId, $userId)
    {
        try {
            $result = $this->game_room_repository->getTotalBetAmountOfTargetUserForSpinWheel($roundId, $userId);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch total bet amount of target user for spin wheel : ' . $e->getMessage());
            throw $e;
        }
    }

    public function getTotalBetAmountOfTargetUserForCoinFlip($roundId, $userId)
    {
        try {
            $result = $this->game_room_repository->getTotalBetAmountOfTargetUserForCoinFlip($roundId, $userId);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch total bet amount of target user for coin flip : ' . $e->getMessage());
            throw $e;
        }
    }
}
