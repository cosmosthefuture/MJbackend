<?php

namespace App\Http\Repositories\User;

use App\Http\Repositories\BaseRepo;
use App\Models\Notification;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;


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

    public function read()
    {
        Notification::where('recipient_type', 'user')
            ->where('recipient_id', auth('api-user')->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}
