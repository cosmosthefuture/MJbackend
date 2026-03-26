<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;
use App\Models\Admin;
use App\Models\PaymentMethod;
use App\Models\Permission;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Str;

class PaymentMethodRepository extends BaseRepo
{
    public function __construct(PaymentMethod $model)
    {
        parent::__construct($model);
    }

    public function find($id)
    {
        $data = PaymentMethod::find($id);
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
