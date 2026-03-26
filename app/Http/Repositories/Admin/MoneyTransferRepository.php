<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;

use App\Models\UserMoneyTransferRecord;

use Exception;
use Illuminate\Support\Facades\DB;

class MoneyTransferRepository extends BaseRepo
{
    public function __construct(UserMoneyTransferRecord $model)
    {
        parent::__construct($model);
    }

    public function find($id)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return null;
        }
        $data->load('sender', 'recipient');
        return $data;
    }
}
