<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\DepositRepository;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Str;

class DepositService
{
    protected $deposit_repository;

    public function __construct(DepositRepository $deposit_repository)
    {
        $this->deposit_repository = $deposit_repository;
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
            $result = $this->deposit_repository->getDataWithPagination(page: $page, perPage: $perPage, with: $with, whereHas: $whereHas);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch user deposit request lists with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getManualDepositLists(
        int $perPage = 10,
        int $page = 1,
        string $orderBy = 'created_at',
        $searches = null,
        array $conditions = [],
        array $orConditions = [],
        array $with = [],
        ?array $whereHas = null,
        ?string $status = null
    ) {
        try {
            $result = $this->deposit_repository->getManualDepositLists(page: $page, per_page: $perPage, with: $with, searches: $searches);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch manual user deposit lists with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findDepositRequest($id)
    {
        $data = $this->deposit_repository->findDepositRequest($id);
        return $data;
    }

    public function approveUserDepositRequest($data)
    {
        DB::beginTransaction();
        try {
            $this->deposit_repository->approveDepositRequest($data);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to approve user deposit request: ' . $e->getMessage());
            throw $e;
        }
    }

    public function rejectUserDepositRequest($data, $reason)
    {
        DB::beginTransaction();
        try {
            $this->deposit_repository->rejectDepositRequest($data, $reason);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to reject user deposit request: ' . $e->getMessage());
            throw $e;
        }
    }

    public function manualCreateUserDeposit($data)
    {
        DB::beginTransaction();
        try {
            $msg = $this->deposit_repository->createDepositManually($data);
            DB::commit();
            return $msg;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to manually create user deposit: ' . $e->getMessage());
            throw $e;
        }
    }

    public function whereLatest($column, $value)
    {
        try {
            $result = $this->deposit_repository->whereLatest($column, $value);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find user deposit req with whereLatest: ' . $e->getMessage());
            throw $e;
        }
    }
}
