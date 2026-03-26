<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;
use App\Models\Admin;
use App\Models\PermissionGroup;
use Str;

class PermissionRepository extends BaseRepo
{
    public function __construct(PermissionGroup $model)
    {
        parent::__construct($model);
    }
}
