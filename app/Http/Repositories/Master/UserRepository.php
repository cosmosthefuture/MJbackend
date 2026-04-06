<?php

namespace App\Http\Repositories\Master;

use App\Http\Repositories\BaseRepo;
use App\Models\MasterWalletRecord;
use App\Models\User;
use App\Models\Permission;
use App\Models\UserDepositRecord;
use App\Models\UserWithdrawRecord;
use Exception;
use Illuminate\Support\Facades\DB;

class UserRepository extends BaseRepo
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function find($id)
    {
        $user = User::find($id);
        if (!$user) {
            return null;
        }
        return $user;
    }

    public function toggleActive($user)
    {
        if ($user->status == 'active') {
            $user->update(['status' => 'inactive']);
        } else {
            $user->update(['status' => 'active']);
        }
    }

    public function addMoneyToUser($data)
    {
        $user = User::find($data['user_id']);
        $master = $user->master;
        $master->withdraw($data['amount']);
        $user->deposit($data['amount']);
        $result = MasterWalletRecord::create([
            'master_id' => $master->id,
            'date_time' => now(),
            'type' => 'out',
            'description' => 'Add Money To User.',
            'amount' => $data['amount'],
            'balance' => $master->balance
        ]);

        // add user deposit record
        UserDepositRecord::create([
            'user_id' => $data['user_id'],
            'action_by_master' => auth('api-master')->user()->id,
            'amount' => $data['amount'],
            'date_time' => now()
        ]);

        return $result;
    }

    public function withdrawMoneyFromUser($data)
    {
        $user = User::find($data['user_id']);
        $master = $user->master;
        $user->withdraw($data['amount']);

        // add user withdraw record
        UserWithdrawRecord::create([
            'user_id' => $data['user_id'],
            'action_by_master' => auth('api-master')->user()->id,
            'amount' => $data['amount'],
            'date_time' => now()
        ]);
    }

    public function getUserDepositLists($page = 1, $per_page = 10, $with = [])
    {
        $offset = ($page - 1) * $per_page;

        $master = auth('api-master')->user();
        $query = UserDepositRecord::with($with)->where('action_by_master', $master->id)
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

    public function getUserWithdrawLists($page = 1, $per_page = 10, $with = [])
    {
        $offset = ($page - 1) * $per_page;

        $master = auth('api-master')->user();
        $query = UserWithdrawRecord::with($with)->where('action_by_master', $master->id)
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
