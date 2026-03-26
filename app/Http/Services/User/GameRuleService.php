<?php

namespace App\Http\Services\User;

use App\Http\Repositories\User\GameRuleRepository;
use Exception;

class GameRuleService
{
    protected $game_rule_repository;

    public function __construct(GameRuleRepository $game_rule_repository)
    {
        $this->game_rule_repository = $game_rule_repository;
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
            $result = $this->game_rule_repository->getDataWithPagination(page: $page, perPage: $perPage, status: $status, searches: $searches);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch game rule data with pagination: ' . $e->getMessage());
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
            $result = $this->game_rule_repository->getDataWithPaginationCached(page: $page, perPage: $perPage, conditions: $conditions);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch game rule data with pagination cached: ' . $e->getMessage());
            throw $e;
        }
    }
}
