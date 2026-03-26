<?php

namespace App\Http\Services\User;

use App\Http\Repositories\User\NotificationRepository;
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
            logger()->error('Error : Failed to fetch notification data with pagination: ' . $e->getMessage());
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

    public function read()
    {
        DB::beginTransaction();
        try {
            $this->notification_repository->read();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to read user notification: ' . $e->getMessage());
            throw $e;
        }
    }
}
