<?php

namespace App\Http\Repositories\Master;

use App\Http\Repositories\BaseRepo;
use App\Models\Agent;
use DB;
use Exception;
use Illuminate\Support\Facades\Hash;

class AgentRepository extends BaseRepo
{
    public function __construct(Agent $model)
    {
        parent::__construct($model);
    }

    public function create($attributes)
    {
        DB::beginTransaction();
        try {
            $data = [
                'name' => $attributes['name'],
                // 'email' => $attributes['email'],
                'username' => $attributes['username'],
                'agent_code' => $attributes['agent_code'],
                'master_id' => auth('api-master')->user()->id,
                'password' => Hash::make($attributes['password']),
                'phone_number' => $attributes['phone_number'],
                'winning_commission_percentage' => $attributes['winning_commission_percentage']
            ];
            $agent = parent::create($data);
            DB::commit();
            return $agent;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, array $attributes)
    {
        DB::beginTransaction();
        try {
            $agent = parent::update($id, $attributes);
            DB::commit();
            return $agent;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function toggleActive($agent)
    {
        if ($agent->status == 'active') {
            $agent->update(['status' => 'inactive']);
        } else {
            $agent->update(['status' => 'active']);
        }
    }
}
