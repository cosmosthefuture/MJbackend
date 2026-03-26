<?php

namespace App\Http\Repositories\Master;

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

    public function read()
    {
        Notification::where('recipient_type', 'master')
            ->where('recipient_id', auth('api-master')->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}
