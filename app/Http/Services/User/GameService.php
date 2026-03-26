<?php

namespace App\Http\Services\User;

use App\Http\Repositories\User\GameRepository;
use Exception;

class GameService
{
    protected $game_repository;

    public function __construct(GameRepository $game_repository)
    {
        $this->game_repository = $game_repository;
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
            $result = $this->game_repository->getDataWithPagination(page: $page, perPage: $perPage, status: $status, searches: $searches);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch game data with pagination: ' . $e->getMessage());
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
            $result = $this->game_repository->getDataWithPaginationCached(page: $page, perPage: $perPage, status: $status, searches: $searches);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch game data with pagination cached: ' . $e->getMessage());
            throw $e;
        }
    }

    public function find(int $id)
    {
        try {
            $result = $this->game_repository->find($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch game: ' . $e->getMessage());
            throw $e;
        }
    }
}
