<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\MasterRepository;
use App\Http\Repositories\BaseRepo;
use App\Models\Master;
use Exception;
use Str;

class MasterService
{
    protected $master_repository;

    public function __construct(MasterRepository $master_repository)
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
        ?string $status = null
    ) {
        try {
            $result = $this->master_repository->getDataWithPagination(page: $page, perPage: $perPage, with: $with, searches: $searches, status: $status);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch master data with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function find(int $id)
    {
        try {
            $result = $this->master_repository->find($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch master: ' . $e->getMessage());
            throw $e;
        }
    }

    public function create(array $attributes)
    {
        try {
            $result = $this->master_repository->create($attributes);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to create master: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update(int $id, array $attributes)
    {
        try {
            $result = $this->master_repository->update($id, $attributes);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to update master: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(int $id)
    {
        try {
            $result = $this->master_repository->delete($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to delete master: ' . $e->getMessage());
            throw $e;
        }
    }

    public function whereFirst($column, $value)
    {
        try {
            $result = $this->master_repository->whereFirst($column, $value);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find master with whereFirst: ' . $e->getMessage());
            throw $e;
        }
    }

    public function toggleMasterStatus($master)
    {
        $this->master_repository->toggleActive($master);
    }

    public function addMoneyToMaster(array $attributes)
    {
        try {
            $result = $this->master_repository->addMoneyToMaster($attributes);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to add money to master: ' . $e->getMessage());
            throw $e;
        }
    }

    public function withdrawMoneyFromMaster(array $attributes)
    {
        try {
            $result = $this->master_repository->withdrawMoneyFromMaster($attributes);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to withdraw money from master: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getMasterDepositLists(
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
            $result = $this->master_repository->getMasterDepositLists(page: $page, per_page: $perPage, with: $with);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch master deposit data with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getMasterWithdrawLists(
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
            $result = $this->master_repository->getMasterWithdrawLists(page: $page, per_page: $perPage, with: $with);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch master withdraw data with pagination: ' . $e->getMessage());
            throw $e;
        }
    }
}
