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
}
