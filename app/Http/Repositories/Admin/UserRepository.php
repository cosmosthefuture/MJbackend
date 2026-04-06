<?php

namespace App\Http\Repositories\Admin;

use App\Http\Repositories\BaseRepo;
use App\Models\Agent;
use App\Models\Master;
use App\Models\User;
use App\Models\Permission;
use Exception;
use Illuminate\Support\Facades\DB;
use Str;

class UserRepository extends BaseRepo
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function create($attributes)
    {
        $data = [
            'name' => $attributes['name'],
            'username' => $attributes['username'] ?? null,
            'email' => $attributes['email'] ?? null,
            'password' => bcrypt(Str::random(16)),
            'phone_number' => $attributes['phone_number'],
            'is_verified' => false,
            'status' => 'inactive'
        ];
        if (isset($attributes['agent_code'])) {
            $agent = Agent::where('agent_code', $attributes['agent_code'])->first();
            $data['agent_id'] = $agent->id;
        }
        if (isset($attributes['master_code'])) {
            $master = Master::where('master_code', $attributes['master_code'])->first();
            $data['master_id'] = $master->id;
        }
        return $this->model->create($data);
    }

    public function find($id)
    {
        $user = User::find($id);
        if (!$user) {
            return null;
        }
        return $user->load(['master', 'agent']);
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
