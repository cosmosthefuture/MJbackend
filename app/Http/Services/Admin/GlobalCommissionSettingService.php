<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\GlobalCommissionSettingRepository;
use Exception;

class GlobalCommissionSettingService
{
    protected $global_commission_setting_repository;

    public function __construct(GlobalCommissionSettingRepository $global_commission_setting_repository)
    {
        $this->global_commission_setting_repository = $global_commission_setting_repository;
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
            $result = $this->global_commission_setting_repository->getDataWithPagination(page: $page, perPage: $perPage);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch global commission setting data with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function find(int $id)
    {
        try {
            $result = $this->global_commission_setting_repository->find($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch global commission setting data: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update($id, $data)
    {
        try {
            $result = $this->global_commission_setting_repository->update($id, $data);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to update global commission setting data: ' . $e->getMessage());
            throw $e;
        }
    }
}
