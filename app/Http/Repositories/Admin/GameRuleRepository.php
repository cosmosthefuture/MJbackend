<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;
use App\Models\GameRule;

class GameRuleRepository extends BaseRepo
{
    public function __construct(GameRule $model)
    {
        parent::__construct($model);
    }

    public function find($id)
    {
        $data = GameRule::with(['game', 'createdBy', 'updatedBy'])->find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function toggleActive($data)
    {
        $admin_id = auth('api-admin')->user()->id;
        if ($data->status == 'active') {
            $data->update(['status' => 'inactive', 'updated_by' => $admin_id]);
        } else {
            $data->update(['status' => 'active', 'updated_by' => $admin_id]);
        }
    }
}
