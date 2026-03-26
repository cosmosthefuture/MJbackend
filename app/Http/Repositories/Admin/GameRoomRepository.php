<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;
use App\Models\GameRoom;

class GameRoomRepository extends BaseRepo
{
    public function __construct(GameRoom $model)
    {
        parent::__construct($model);
    }

    public function find($id)
    {
        $data = GameRoom::with(['game', 'createdBy', 'gameRule'])->find($id);
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
