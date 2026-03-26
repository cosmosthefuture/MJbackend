<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;
use App\Models\User;
use App\Models\Permission;
use Exception;
use Illuminate\Support\Facades\DB;

class UserRepository extends BaseRepo
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function find($id)
    {
        $user = User::find($id);
        if (!$user) {
            return null;
        }
        return $user;
    }

    public function toggleActive($user)
    {
        if ($user->status == 'active') {
            $user->update(['status' => 'inactive']);
        } else {
            $user->update(['status' => 'active']);
        }
    }
}
