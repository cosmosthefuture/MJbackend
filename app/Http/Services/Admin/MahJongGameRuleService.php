<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\MahJongGameRuleRepository;
use Exception;

class MahJongGameRuleService
{
    protected $game_rule_repository;

    public function __construct(MahJongGameRuleRepository $game_rule_repository)
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
            $result = $this->game_rule_repository->getDataWithPagination(page: $page, perPage: $perPage, with: $with, status: $status, searches: $searches, conditions: $conditions);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch game rule data with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function find(int $id)
    {
        try {
            $result = $this->game_rule_repository->find($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch game rule: ' . $e->getMessage());
            throw $e;
        }
    }

    public function create(array $attributes)
    {
        try {
            $attributes['status'] = 'inactive';
            $attributes['created_by'] = auth('api-admin')->user()->id;
            $result = $this->game_rule_repository->create($attributes);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to create game rule: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update(int $id, array $attributes)
    {
        try {
            $attributes['updated_by'] = auth('api-admin')->user()->id;
            $result = $this->game_rule_repository->update($id, $attributes);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to update game rule: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(int $id)
    {
        try {
            $result = $this->game_rule_repository->delete($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to delete game rule: ' . $e->getMessage());
            throw $e;
        }
    }

    public function whereFirst($column, $value)
    {
        try {
            $result = $this->game_rule_repository->whereFirst($column, $value);
            if(!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find game rule with whereFirst: ' . $e->getMessage());
            throw $e;
        }
    }

    public function toggleGameRuleStatus($data)
    {
        $this->game_rule_repository->toggleActive($data);
    }
}
