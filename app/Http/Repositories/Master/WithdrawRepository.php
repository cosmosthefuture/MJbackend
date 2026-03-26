<?php

namespace App\Http\Repositories\Master;

use App\Http\Repositories\BaseRepo;

use App\Models\Master;
use App\Models\MasterWithdrawRequest;
use App\Models\Notification;
use App\Models\Permission;
use Exception;
use Illuminate\Support\Facades\DB;

class WithdrawRepository extends BaseRepo
{
    public function __construct(MasterWithdrawRequest $model)
    {
        parent::__construct($model);
    }

    public function getWithdrawHistory($masterId, $page, $per_page, $with)
    {
        $query = MasterWithdrawRequest::with($with)
            ->where('master_id', $masterId)
            ->orderByDesc('created_at');

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

    public function whereLatest($column, $value)
    {
        $data = MasterWithdrawRequest::where($column, $value)->latest()->first();
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function get_admins_for_noti($origin_data)
    {
        $adminIds = Permission::whereHas('permissionType.group', function ($q) {
            $q->where('name', 'master_withdraw_management');
        })
            ->pluck('admin_id')
            ->unique()
            ->values();

        $notifications = $adminIds->map(function ($adminId) use ($origin_data) {
            return [
                'recipient_type' => 'admin',
                'recipient_id' => $adminId,
                'type' => 'master_withdraw',
                'title' => 'Master Withdraw Request',
                'message' => $origin_data->master->name . ' requested a new withdraw.',
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
}
