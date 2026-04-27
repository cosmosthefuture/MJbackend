<?php

namespace App\Http\Services\User;

use App\Http\Repositories\User\MahJongGameRoomRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class MahJongGameRoomService
{
    protected $game_room_repository;

    public function __construct(MahJongGameRoomRepository $game_room_repository)
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

    public function getCurrentMatch($roomId)
    {
        try {
            $result = $this->game_room_repository->getCurrentMatch($roomId);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find game room with whereFirst: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createNewMatch($roomId, $rule)
    {
        try {
            $result = $this->game_room_repository->createNewMatch($roomId, $rule);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to create game match: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findCurrentRound($roomId)
    {
        try {
            $result = $this->game_room_repository->findCurrentRound($roomId);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find current game round with room id: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findRound($roundId)
    {
        try {
            $result = $this->game_room_repository->findRound($roundId);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find game round with round id: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createNewRound($roomId)
    {
        try {
            $result = $this->game_room_repository->createNewRound($roomId);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to create game round: ' . $e->getMessage());
            throw $e;
        }
    }

    public function get_player_count($roomId)
    {
        try {
            $result = $this->game_room_repository->get_player_count($roomId);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to get mj player count: ' . $e->getMessage());
            throw $e;
        }
    }

    public function addUserIntoRoomPlayers($roomId, $userId)
    {
        try {
            $this->game_room_repository->addUserIntoRoomPlayers($roomId, $userId);
        } catch (Exception $e) {
            logger()->error('Error : Failed to get mj player count: ' . $e->getMessage());
            throw $e;
        }
    }

    public function assign_seat_positions($room, $round)
    {
        try {
            $data = $this->game_room_repository->assign_seat_positions($room, $round);
            return $data;
        } catch (Exception $e) {
            logger()->error('Error : Failed to assign seat positions to players: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateStatusOfLeaveUser($roomId, $userId)
    {
        try {
            $this->game_room_repository->updateStatusOfLeaveUser($roomId, $userId);
        } catch (Exception $e) {
            logger()->error('Error : Failed to update status of leave user: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateActiveStatusOfRejoinUser($roundId, $userId)
    {
        try {
            $this->game_room_repository->updateActiveStatusOfRejoinUser($roundId, $userId);
        } catch (Exception $e) {
            logger()->error('Error : Failed to update status of rejoined user: ' . $e->getMessage());
            throw $e;
        }
    }

    public function endRound($round)
    {
        try {
            $this->game_room_repository->endRound($round);
        } catch (Exception $e) {
            logger()->error('Error : Failed to end game round: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getShuffledTiles()
    {
        try {
            return $this->game_room_repository->getShuffledTiles();
        } catch (Exception $e) {
            logger()->error('Error : Failed to get shuffled ties: ' . $e->getMessage());
            throw $e;
        }
    }

    public function generateJwtTokenToJoinRoom($user, $roomId)
    {
        $token = JWTAuth::customClaims([
            'purpose' => 'mahjong_join',
            'user_id' => $user->id,
            'room_id' => $roomId,
            'iat' => now()->timestamp,
            'exp' => now()->addSeconds(10)->timestamp
        ])->fromUser($user);

        return $token;
    }
}
