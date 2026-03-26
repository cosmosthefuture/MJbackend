<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;
use App\Models\Game;


class GameRepository extends BaseRepo
{
    public function __construct(Game $model)
    {
        parent::__construct($model);
    }

    public function find($id)
    {
        $data = Game::find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function toggleActive($data)
    {
        if ($data->status == 'active') {
            $data->update(['status' => 'inactive']);
        } else {
            $data->update(['status' => 'active']);
        }
    }
}
