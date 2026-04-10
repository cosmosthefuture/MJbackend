<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;
use App\Models\Master;
use App\Models\MasterDepositRecord;
use App\Models\MasterWalletRecord;
use App\Models\MasterWithdrawRecord;
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
                'winning_commission_percentage' => $attributes['winning_commission_percentage'],
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
            $attributes['password'] = Hash::make($attributes['password']);
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

    public function addMoneyToMaster($data)
    {
        $master = Master::find($data['master_id']);
        $master->deposit($data['amount']);
        $balance = $master->balance;
        $data['date_time'] = now();
        $data['description'] = "Money Added By Admin.";
        $data['balance'] = $balance;
        $data['type'] = 'in';

        $result = MasterWalletRecord::create($data);

        // add to master deposit record
        MasterDepositRecord::create([
            'master_id' => $data['master_id'],
            'action_by' => auth('api-admin')->user()->id,
            'amount' => $data['amount'],
            'date_time' => now()
        ]);

        return $result;
    }

    public function withdrawMoneyFromMaster($data)
    {
        $master = Master::find($data['master_id']);
        $master->withdraw($data['amount']);
        $balance = $master->balance;
        $data['date_time'] = now();
        $data['description'] = "Money Withdrawed By Admin.";
        $data['balance'] = $balance;
        $data['type'] = 'out';

        $result = MasterWalletRecord::create($data);

        // add to master withdraw record
        MasterWithdrawRecord::create([
            'master_id' => $data['master_id'],
            'action_by' => auth('api-admin')->user()->id,
            'amount' => $data['amount'],
            'date_time' => now()
        ]);

        return $result;
    }

    public function getMasterDepositLists($page = 1, $per_page = 10, $with = [])
    {
        $offset = ($page - 1) * $per_page;

        $query = MasterDepositRecord::with($with)
            ->orderBy('date_time', 'desc');

        $totalCount = $query->count();

        $results = $query
            ->skip($offset)
            ->take($per_page)
            ->get();

        $totalPages = (int) ceil($totalCount / $per_page);

        return [
            'data' => $results,
            'meta' => [
                'total' => $totalCount,
                'per_page' => $per_page,
                'current_page' => $page,
                'total_pages' => $totalPages,
            ],
        ];
    }

    public function getMasterWithdrawLists($page = 1, $per_page = 10, $with = [])
    {
        $offset = ($page - 1) * $per_page;

        $query = MasterWithdrawRecord::with($with)
            ->orderBy('date_time', 'desc');

        $totalCount = $query->count();

        $results = $query
            ->skip($offset)
            ->take($per_page)
            ->get();

        $totalPages = (int) ceil($totalCount / $per_page);

        return [
            'data' => $results,
            'meta' => [
                'total' => $totalCount,
                'per_page' => $per_page,
                'current_page' => $page,
                'total_pages' => $totalPages,
            ],
        ];
    }
}
