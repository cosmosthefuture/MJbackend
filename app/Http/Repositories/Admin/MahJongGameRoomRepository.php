<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;
use App\Models\MahJongGameRoom;

class MahJongGameRoomRepository extends BaseRepo
{
    public function __construct(MahJongGameRoom $model)
    {
        parent::__construct($model);
    }

    public function find($id)
    {
        $data = MahJongGameRoom::with(['game', 'createdBy', 'gameRule.fees'])->find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function toggleActive($data)
    {
        $admin_id = auth('api-admin')->user()->id;
        if ($data->status == 'open') {
            $data->update(['status' => 'closed']);
        } else {
            $data->update(['status' => 'open']);
        }
    }
}
