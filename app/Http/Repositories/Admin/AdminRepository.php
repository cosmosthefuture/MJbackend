<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;
use App\Models\Admin;
use App\Models\Permission;
use App\Models\UserFcmToken;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Str;

class AdminRepository extends BaseRepo
{
    public function __construct(Admin $model)
    {
        parent::__construct($model);
    }

    public function generateAccessToken(Admin $admin)
    {
        $admin->tokens()->delete();
        return $admin->createToken($admin->username . '_AccessToken', [''], now()->addDays(2))->plainTextToken;
    }

    public function generateRefreshToken(Admin $admin)
    {
        return $admin->createToken($admin->username . '_RefreshToken', [''], now()->addWeek())->plainTextToken;
    }

    public function create($attributes)
    {
        DB::beginTransaction();
        try {
            $data = [
                'name' => $attributes['name'],
                // 'email' => $attributes['email'],
                'username' => $attributes['username'],
                'password' => Hash::make($attributes['password']),
                'phone_number' => $attributes['phone_number']
            ];
            $admin = parent::create($data);
            foreach ($attributes['permission_type_ids'] as $each) {
                Permission::create([
                    'admin_id' => $admin->id,
                    'permission_type_id' => $each
                ]);
            }
            DB::commit();
            return $admin;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, array $attributes)
    {
        DB::beginTransaction();
        try {
            $admin = parent::update($id, $attributes);
            Permission::where('admin_id', $admin->id)->delete();
            foreach ($attributes['permission_type_ids'] as $each) {
                Permission::create([
                    'admin_id' => $admin->id,
                    'permission_type_id' => $each
                ]);
            }
            DB::commit();
            return $admin;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function find($id)
    {
        $admin = Admin::with('permissions.permissionType.group')->find($id);
        if (!$admin) {
            return null;
        }
        return $admin;
    }

    public function toggleActive($admin)
    {
        if ($admin->status == 'active') {
            $admin->update(['status' => 'inactive']);
        } else {
            $admin->update(['status' => 'active']);
        }
    }

    public function storeFcmToken($admin, $token)
    {
        $result = UserFcmToken::updateOrCreate(
            [
                'token' => $token,
            ],
            [
                'recipient_type' => 'admin',
                'recipient_id' => $admin->id,
            ]
        );
        return $result;
    }
}
