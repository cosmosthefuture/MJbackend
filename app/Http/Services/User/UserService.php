<?php

namespace App\Http\Services\User;

use App\Http\Repositories\User\UserRepository;
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

    public function register(array $attributes)
    {
        try {
            $result = $this->user_repository->create($attributes);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to register user: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createByAdmin(array $attributes)
    {
        try {
            $data = [
                'name' => $attributes['name'],
                'username' => $attributes['username'],
                'email' => $attributes['email'],
                'password' => bcrypt(Str::random(16)),
                'phone_number' => $attributes['phone_number'],
                'is_verified' => false,
                'status' => 'inactive'
            ];
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

    public function generateAccessToken($user)
    {
        try {
            $result = $this->user_repository->generateAccessToken($user);
            $user->update(['last_logined' => now()]);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to generate user access token: ' . $e->getMessage());
            throw $e;
        }
    }

    public function changePassword($data)
    {
        try {
            $this->user_repository->changePassword($data);
        } catch (Exception $e) {
            logger()->error('Error : Failed to change user password: ' . $e->getMessage());
            throw $e;
        }
    }

    public function resetPassword($data)
    {
        try {
            $result = $this->user_repository->resetPassword($data);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to reset user password: ' . $e->getMessage());
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
            logger()->error('Error : Failed to user logout: ' . $e->getMessage());
            throw $e;
        }
    }

    public function storeFcmToken($user, $token)
    {
        try {
            $result = $this->user_repository->storeFcmToken($user, $token);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to store user fcm token: ' . $e->getMessage());
            throw $e;
        }
    }
}
