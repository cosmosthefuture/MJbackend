<?php

namespace App\Http\Repositories\Master;

use App\Http\Repositories\BaseRepo;
use App\Models\Agent;
use App\Models\AgentWalletRecord;
use App\Models\MasterWalletRecord;
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

    public function addMoneyToAgent($data)
    {
        $agent = Agent::find($data['agent_id']);
        $agent->deposit($data['amount']);
        $balance = $agent->balance;
        $data['date_time'] = now();
        $data['type'] = 'in';
        $data['description'] = "Money Added By Master.";
        $data['balance'] = $balance;

        $result = AgentWalletRecord::create($data);

        $master = $agent->master;
        $master->withdraw($data['amount']);
        MasterWalletRecord::create([
            'master_id' => $master->id,
            'date_time' => now(),
            'type' => 'out',
            'description' => 'Add Money To Agent.',
            'amount' => $data['amount'],
            'balance' => $master->balance
        ]);
        return $result;
    }
}
