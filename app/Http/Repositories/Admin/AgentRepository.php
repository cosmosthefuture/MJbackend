<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;
use App\Models\Agent;

class AgentRepository extends BaseRepo
{
    public function __construct(Agent $model)
    {
        parent::__construct($model);
    }
}
