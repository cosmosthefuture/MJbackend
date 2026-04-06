<?php

namespace App\Http\Repositories\User;

use App\Http\Repositories\BaseRepo;
use App\Models\Agent;
use App\Models\Master;
use App\Models\User;
use App\Models\Permission;
use App\Models\UserFcmToken;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseRepo
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function generateAccessToken(User $user)
    {
        $user->tokens()->delete();
        return $user->createToken($user->username . '_AccessToken', [''], now()->addDays(2))->plainTextToken;
    }

    public function generateRefreshToken(User $user)
    {
        return $user->createToken($user->username . '_RefreshToken', [''], now()->addWeek())->plainTextToken;
    }

    public function create($attributes)
    {
        $data = [
            'name' => $attributes['name'],
            'username' => $attributes['username'] ?? null,
            'email' => $attributes['email'] ?? null,
            'password' => Hash::make($attributes['password']),
            'phone_number' => $attributes['phone_number'],
            'is_verified' => true
        ];
        if (isset($attributes['agent_code'])) {
            $agent = Agent::where('agent_code', $attributes['agent_code'])->first();
            $data['agent_id'] = $agent->id;
        }
        if (isset($attributes['master_code'])) {
            $master = Master::where('master_code', $attributes['master_code'])->first();
            $data['master_id'] = $master->id;
        }
        return $this->model->create($data);
    }

    public function find($id)
    {
        $user = User::find($id);
        if (!$user) {
            return null;
        }
        return $user;
    }

    public function changePassword($data)
    {
        $user = auth('api-user')->user();
        $user->password = Hash::make(value: $data['new_password']);
        $user->save();
        // $user->tokens()->delete();
    }

    public function resetPassword($data)
    {
        $user = User::where('phone_number', $data['phone_number'])->first();
        $user->password = Hash::make(value: $data['new_password']);
        $user->save();
        $user->tokens()->delete();
        return $user->createToken($user->username . '_AccessToken', [''], now()->addDays(2))->plainTextToken;
    }

    public function storeFcmToken($user, $token)
    {
        $result = UserFcmToken::updateOrCreate(
            [
                'token' => $token,
            ],
            [
                'recipient_type' => 'user',
                'recipient_id' => $user->id,
            ]
        );
        return $result;
    }
}
