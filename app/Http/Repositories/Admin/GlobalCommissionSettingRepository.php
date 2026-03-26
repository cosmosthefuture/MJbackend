<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;
use App\Models\GlobalCommissionSetting;


class GlobalCommissionSettingRepository extends BaseRepo
{
    public function __construct(GlobalCommissionSetting $model)
    {
        parent::__construct($model);
    }

    public function find($id)
    {
        $data = GlobalCommissionSetting::find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }
}
