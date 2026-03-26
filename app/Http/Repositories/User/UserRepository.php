<?php

namespace App\Http\Repositories\User;

use App\Http\Repositories\BaseRepo;
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
