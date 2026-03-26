<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;
use App\Models\Master;
use App\Models\Permission;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Str;

class MasterRepository extends BaseRepo
{
    public function __construct(Master $model)
    {
        parent::__construct($model);
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
                'phone_number' => $attributes['phone_number'],
                'winning_commission_percentage' => $attributes['winning_commission_percentage']
            ];
            $master = parent::create($data);
            DB::commit();
            return $master;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, array $attributes)
    {
        DB::beginTransaction();
        try {
            $master = parent::update($id, $attributes);
            DB::commit();
            return $master;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function toggleActive($master)
    {
        if ($master->status == 'active') {
            $master->update(['status' => 'inactive']);
        } else {
            $master->update(['status' => 'active']);
        }
    }
}
