<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\AgentRepository;
use Exception;


class AgentService
{
    protected $master_repository;

    public function __construct(AgentRepository $master_repository)
    {
        $this->master_repository = $master_repository;
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
            $result = $this->master_repository->getDataWithPagination(page: $page, perPage: $perPage, with: $with, searches: $searches, status: $status);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch agent data with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function find(int $id)
    {
        try {
            $result = $this->master_repository->find($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch agent: ' . $e->getMessage());
            throw $e;
        }
    }
}
