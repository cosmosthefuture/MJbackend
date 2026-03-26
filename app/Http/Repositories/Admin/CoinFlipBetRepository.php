<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;
use App\Models\CoinFlipBet;


class CoinFlipBetRepository extends BaseRepo
{
    public function __construct(CoinFlipBet $model)
    {
        parent::__construct($model);
    }

    public function find($id)
    {
        $data = CoinFlipBet::find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }
}
