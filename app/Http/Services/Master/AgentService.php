<?php

namespace App\Http\Services\Master;

use App\Http\Repositories\Master\AgentRepository;
use App\Http\Repositories\BaseRepo;
use App\Models\Agent;
use Exception;
use Str;

class AgentService
{
    protected $agent_repository;

    public function __construct(AgentRepository $agent_repository)
    {
        $this->agent_repository = $agent_repository;
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
            $result = $this->agent_repository->getDataWithPagination(page: $page, perPage: $perPage, with: $with, searches: $searches, status: $status);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch agent data with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function find(int $id)
    {
        try {
            $result = $this->agent_repository->find($id);
            if($result) {
                $result->load('master');
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch agent: ' . $e->getMessage());
            throw $e;
        }
    }

    public function create(array $attributes)
    {
        try {
            $result = $this->agent_repository->create($attributes);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to create agent: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update(int $id, array $attributes)
    {
        try {
            $result = $this->agent_repository->update($id, $attributes);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to update agent: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(int $id)
    {
        try {
            $result = $this->agent_repository->delete($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to delete agent: ' . $e->getMessage());
            throw $e;
        }
    }

    public function whereFirst($column, $value)
    {
        try {
            $result = $this->agent_repository->whereFirst($column, $value);
            if(!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find agent with whereFirst: ' . $e->getMessage());
            throw $e;
        }
    }

    public function toggleAgentStatus($agent)
    {
        $this->agent_repository->toggleActive($agent);
    }
}
