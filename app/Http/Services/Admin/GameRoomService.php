<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\GameRoomRepository;
use Exception;

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
        ?string $status = null)
    {
        try {
            $result = $this->game_room_repository->getDataWithPagination(page: $page, perPage: $perPage, with: $with, status: $status, searches: $searches, conditions: $conditions);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch game room data with pagination: ' . $e->getMessage());
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

    public function create(array $attributes)
    {
        try {
            $attributes['status'] = 'closed';
            $attributes['created_by'] = auth('api-admin')->user()->id;
            $result = $this->game_room_repository->create($attributes);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to create game room: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update(int $id, array $attributes)
    {
        try {
            $result = $this->game_room_repository->update($id, $attributes);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to update game room: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(int $id)
    {
        try {
            $result = $this->game_room_repository->delete($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to delete game room: ' . $e->getMessage());
            throw $e;
        }
    }

    public function whereFirst($column, $value)
    {
        try {
            $result = $this->game_room_repository->whereFirst($column, $value);
            if(!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find game room with whereFirst: ' . $e->getMessage());
            throw $e;
        }
    }

    public function toggleGameRoomStatus($data)
    {
        $this->game_room_repository->toggleActive($data);
    }
}
