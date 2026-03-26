<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\PermissionRepository;
use App\Http\Repositories\BaseRepo;
use App\Models\Admin;
use Exception;
use Str;

class PermissionService
{
    protected $permission_repository;

    public function __construct(PermissionRepository $permission_repository)
    {
        $this->permission_repository = $permission_repository;
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
            $result = $this->permission_repository->getDataWithPagination(page: $page, perPage: $perPage, with: $with);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch permission data with pagination: ' . $e->getMessage());
            throw $e;
        }
    }
}
