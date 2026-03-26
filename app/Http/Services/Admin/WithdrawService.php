<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\WithdrawRepository;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Str;

class WithdrawService
{
    protected $withdraw_repository;

    public function __construct(WithdrawRepository $withdraw_repository)
    {
        $this->withdraw_repository = $withdraw_repository;
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
            $result = $this->withdraw_repository->getDataWithPagination(page: $page, perPage: $perPage, with: $with, whereHas: $whereHas);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch user withdraw request lists with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getManualWithdrawLists(
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
            $result = $this->withdraw_repository->getManualWithdrawLists(page: $page, per_page: $perPage, with: $with, searches: $searches);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch manual user withdraw lists with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findWithdrawRequest($id)
    {
        $data = $this->withdraw_repository->findWithdrawRequest($id);
        return $data;
    }

    public function approveUserWithdrawRequest($data)
    {
        DB::beginTransaction();
        try {
            $this->withdraw_repository->approveWithdrawRequest($data);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to approve user withdraw request: ' . $e->getMessage());
            throw $e;
        }
    }

    public function rejectUserWithdrawRequest($data, $reason)
    {
        DB::beginTransaction();
        try {
            $this->withdraw_repository->rejectWithdrawRequest($data, $reason);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to reject user withdraw request: ' . $e->getMessage());
            throw $e;
        }
    }

    public function manualCreateUserWithdraw($data)
    {
        DB::beginTransaction();
        try {
            $msg = $this->withdraw_repository->createWithdrawManually($data);
            DB::commit();
            return $msg;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to manually create user withdraw: ' . $e->getMessage());
            throw $e;
        }
    }

    public function whereLatest($column, $value)
    {
        try {
            $result = $this->withdraw_repository->whereLatest($column, $value);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find user withdraw req with whereLatest: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getAgentWithdrawRequestsWithPagination(
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
            $result = $this->withdraw_repository->getAgentRequestData(page: $page, per_page: $perPage, with: $with, searches: $searches);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch user withdraw request lists with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findAgentWithdrawRequest($id)
    {
        $data = $this->withdraw_repository->findAgentWithdrawRequest($id);
        return $data;
    }

    public function approveAgentWithdrawRequest($data)
    {
        DB::beginTransaction();
        try {
            $this->withdraw_repository->approveAgentWithdrawRequest($data);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to approve agent withdraw request: ' . $e->getMessage());
            throw $e;
        }
    }

    public function rejectAgentWithdrawRequest($data, $reason)
    {
        DB::beginTransaction();
        try {
            $this->withdraw_repository->rejectAgentWithdrawRequest($data, $reason);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to reject agent withdraw request: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findAgentWithdraw($id)
    {
        $data = $this->withdraw_repository->findAgentWithdraw($id);
        return $data;
    }

    public function getMasterWithdrawRequestsWithPagination(
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
            $result = $this->withdraw_repository->getMasterRequestData(page: $page, per_page: $perPage, with: $with, searches: $searches);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch master withdraw request lists with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findMasterWithdrawRequest($id)
    {
        $data = $this->withdraw_repository->findMasterWithdrawRequest($id);
        return $data;
    }

    public function approveMasterWithdrawRequest($data)
    {
        DB::beginTransaction();
        try {
            $this->withdraw_repository->approveMasterWithdrawRequest($data);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to approve master withdraw request: ' . $e->getMessage());
            throw $e;
        }
    }

    public function rejectMasterWithdrawRequest($data, $reason)
    {
        DB::beginTransaction();
        try {
            $this->withdraw_repository->rejectMasterWithdrawRequest($data, $reason);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to reject master withdraw request: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findMasterWithdraw($id)
    {
        $data = $this->withdraw_repository->findMasterWithdraw($id);
        return $data;
    }
}
