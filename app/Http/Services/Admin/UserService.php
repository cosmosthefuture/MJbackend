<?php

namespace App\Http\Services\Admin;

use App\Http\Repositories\Admin\UserRepository;
use App\Http\Repositories\BaseRepo;
use App\Models\Agent;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Str;

class UserService
{
    protected $user_repository;

    public function __construct(UserRepository $user_repository)
    {
        $this->user_repository = $user_repository;
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
            $result = $this->user_repository->getDataWithPagination(page: $page, perPage: $perPage, with: $with, searches: $searches, status: $status, conditions:$conditions);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch user data with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function find(int $id)
    {
        try {
            $result = $this->user_repository->find($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch user: ' . $e->getMessage());
            throw $e;
        }
    }


    public function createByAdmin(array $attributes)
    {
        try {
            $data = [
                'name' => $attributes['name'],
                'username' => $attributes['username'] ?? null,
                'email' => $attributes['email'] ?? null,
                'password' => bcrypt(Str::random(16)),
                'phone_number' => $attributes['phone_number'],
                'is_verified' => false,
                'status' => 'inactive'
            ];
            if(isset($attributes['agent_code'])) {
                $data['agent_code'] = $attributes['agent_code'];
            } else {
                $default_agent = Agent::where('is_default', 1)->first();
                $data['agent_code'] = $default_agent->agent_code;
            }
            $result = $this->user_repository->create($data);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to manually create user: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update(int $id, array $attributes)
    {
        try {
            $result = $this->user_repository->update($id, $attributes);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to update user: ' . $e->getMessage());
            throw $e;
        }
    }

    public function verifyUser(int $id, array $attributes)
    {
        try {
            $data = [
                'password' => Hash::make($attributes['password']),
                'status' => 'active',
                'is_verified' => true
            ];
            $result = $this->user_repository->update($id, $data);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to verify user: ' . $e->getMessage());
            throw $e;
        }
    }

    public function resetUserPasswordByAdmin(int $id, array $attributes)
    {
        try {
            $data = [
                'password' => Hash::make($attributes['password']),
            ];
            $result = $this->user_repository->update($id, $data);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to reset user password by admin: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(int $id)
    {
        try {
            $result = $this->user_repository->delete($id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to delete user: ' . $e->getMessage());
            throw $e;
        }
    }

    public function whereFirst($column, $value)
    {
        try {
            $result = $this->user_repository->whereFirst($column, $value);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find user with whereFirst: ' . $e->getMessage());
            throw $e;
        }
    }

    public function toggleUserStatus($user)
    {
        $this->user_repository->toggleActive($user);
    }
}
