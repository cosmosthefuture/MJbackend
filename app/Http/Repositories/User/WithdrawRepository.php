<?php

namespace App\Http\Repositories\User;

use App\Http\Repositories\BaseRepo;

use App\Models\ManualUserWithdrawRecord;
use App\Models\Notification;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserWithdrawRequest;
use Exception;
use Illuminate\Support\Facades\DB;

class WithdrawRepository extends BaseRepo
{
    public function __construct(UserWithdrawRequest $model)
    {
        parent::__construct($model);
    }

    public function whereLatest($column, $value)
    {
        $data = UserWithdrawRequest::where($column, $value)->latest()->first();
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function get_admins_for_noti($origin_data)
    {
        $adminIds = Permission::whereHas('permissionType.group', function ($q) {
            $q->where('name', 'user_withdraw_management');
        })
            ->pluck('admin_id')
            ->unique()
            ->values();

        $notifications = $adminIds->map(function ($adminId) use ($origin_data) {
            return [
                'recipient_type' => 'admin',
                'recipient_id' => $adminId,
                'type' => 'user_withdraw',
                'title' => 'Withdraw Request',
                'message' => $origin_data->user->name . ' requested a new withdraw.',
                'data' => $origin_data,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        Notification::insert($notifications);

        return $adminIds->map(function ($adminId) {
            return [
                'recipient_type' => 'admin',
                'recipient_id' => $adminId,
            ];
        })->values();
    }

    public function getManualWithdraws($page, $per_page, $with)
    {
        $query = ManualUserWithdrawRecord::with($with)
            ->orderByDesc('created_at');

        $query->where('user_id', auth('api-user')->user()->id);

        $totalCount = $query->count();

        $offset = ($page - 1) * $per_page;

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
