<?php

namespace App\Http\Repositories\Agent;

use App\Http\Repositories\BaseRepo;
use App\Models\PaymentMethod;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;


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
}
