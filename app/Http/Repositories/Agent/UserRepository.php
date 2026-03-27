<?php

namespace App\Http\Repositories\Agent;

use App\Http\Repositories\BaseRepo;
use App\Models\AgentWalletRecord;
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

    public function addMoneyToUser($data)
    {
        $user = User::find($data['user_id']);
        $agent = $user->agent;
        $agent->withdraw($data['amount']);
        $user->deposit($data['amount']);
        $result = AgentWalletRecord::create([
            'agent_id' => $agent->id,
            'date_time' => now(),
            'type' => 'out',
            'description' => 'Add Money To User.',
            'amount' => $data['amount'],
            'balance' => $agent->balance
        ]);
        return $result;
    }
}
