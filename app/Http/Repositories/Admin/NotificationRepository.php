<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;
use App\Models\Notification;

class NotificationRepository extends BaseRepo
{
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

    public function find($id)
    {
        $data = Notification::find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function getWithdrawNotiWithPagination($perPage = 20, $page = 1, $adminId)
    {
        $query = Notification::query()
            ->where('recipient_type', 'admin')
            ->where('recipient_id', $adminId)
            ->whereIn('type', ['user_withdraw', 'agent_withdraw', 'master_withdraw'])
            ->orderByDesc('created_at');

        $total = $query->count();

        $data = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage),
            ],
        ];
    }

    public function readDepositNoti()
    {
        Notification::where('recipient_type', 'admin')
            ->where('recipient_id', auth('api-admin')->user()->id)
            ->where('type', 'user_deposit')
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function readWithdrawNoti()
    {
        Notification::where('recipient_type', 'admin')
            ->where('recipient_id', auth('api-admin')->user()->id)
            ->whereIn('type', ['user_withdraw', 'agent_withdraw', 'master_withdraw'])
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}
