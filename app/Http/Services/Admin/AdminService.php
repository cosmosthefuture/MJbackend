<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\AdminRepository;
use App\Http\Repositories\BaseRepo;
use App\Models\Admin;
use Exception;
use Str;

class AdminService
{
    protected $admin_repository;

    public function __construct(AdminRepository $admin_repository)
    {
        $this->admin_repository = $admin_repository;
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
            $result = $this->admin_repository->getDataWithPagination(page: $page, perPage: $perPage, with: $with, searches: $searches, status: $status);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch admin data with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function find(int $id)
    {
        try {
            $result = $this->admin_repository->find($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch admin: ' . $e->getMessage());
            throw $e;
        }
    }

    public function create(array $attributes)
    {
        try {
            $result = $this->admin_repository->create($attributes);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to create admin: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update(int $id, array $attributes)
    {
        try {
            $result = $this->admin_repository->update($id, $attributes);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to update admin: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(int $id)
    {
        try {
            $result = $this->admin_repository->delete($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to delete admin: ' . $e->getMessage());
            throw $e;
        }
    }

    public function whereFirst($column, $value)
    {
        try {
            $result = $this->admin_repository->whereFirst($column, $value);
            if(!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find admin with whereFirst: ' . $e->getMessage());
            throw $e;
        }
    }

    public function generateAccessToken($admin)
    {
        try {
            $result = $this->admin_repository->generateAccessToken($admin);
            $admin->update(['last_logined' => now()]);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to generate admin access token: ' . $e->getMessage());
            throw $e;
        }
    }

    public function logout()
    {
        try {
            $currentAccessToken = auth()->user()->currentAccessToken();

            $currentAccessToken->delete();

            auth()->user()->tokens()->where(
                'id',
                $currentAccessToken->id
            )->delete();
            return true;
        } catch (Exception $e) {
            logger()->error('Error : Failed to admin logout: ' . $e->getMessage());
            throw $e;
        }
    }

    public function toggleAdminStatus($admin)
    {
        $this->admin_repository->toggleActive($admin);
    }

    public function storeFcmToken($admin, $token)
    {
        try {
            $result = $this->admin_repository->storeFcmToken($admin, $token);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to store admin fcm token: ' . $e->getMessage());
            throw $e;
        }
    }
}
