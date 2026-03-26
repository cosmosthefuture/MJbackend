<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\NotificationRepository;
use DB;
use Exception;

class NotificationService
{
    protected $notification_repository;

    public function __construct(NotificationRepository $notification_repository)
    {
        $this->notification_repository = $notification_repository;
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
            $result = $this->notification_repository->getDataWithPagination(page: $page, perPage: $perPage, conditions: $conditions);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch deposit notifications with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function find(int $id)
    {
        try {
            $result = $this->notification_repository->find($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch notification: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getWithdrawNotiWithPagination(
        int $per_page = 10,
        int $page = 1,
        int $adminId
    )
    {
        try {
            $result = $this->notification_repository->getWithdrawNotiWithPagination($per_page, $page, $adminId);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch withdraw notifications with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function readDepositNoti()
    {
        DB::beginTransaction();
        try {
            $this->notification_repository->readDepositNoti();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to read admin deposit notification: ' . $e->getMessage());
            throw $e;
        }
    }

    public function readWithdrawNoti()
    {
        DB::beginTransaction();
        try {
            $this->notification_repository->readWithdrawNoti();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to read admin withdraw notification: ' . $e->getMessage());
            throw $e;
        }
    }
}
