<?php

namespace App\Http\Repositories\Master;

use App\Http\Repositories\BaseRepo;
use App\Models\Agent;
use App\Models\AgentDepositRecord;
use App\Models\AgentWalletRecord;
use App\Models\AgentWithdrawRecord;
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
            $attributes['password'] = Hash::make($attributes['password']);
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

        // add to agent deposit record
        AgentDepositRecord::create([
            'agent_id' => $data['agent_id'],
            'action_by' => auth('api-master')->user()->id,
            'amount' => $data['amount'],
            'date_time' => now()
        ]);

        // 
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

    public function withdrawMoneyFromAgent($data)
    {
        $agent = Agent::find($data['agent_id']);
        $agent->withdraw($data['amount']);
        $balance = $agent->balance;
        $data['date_time'] = now();
        $data['type'] = 'out';
        $data['description'] = "Money Withdrawed By Master.";
        $data['balance'] = $balance;

        $result = AgentWalletRecord::create($data);

        // add to agent withdraw record
        AgentWithdrawRecord::create([
            'agent_id' => $data['agent_id'],
            'action_by' => auth('api-master')->user()->id,
            'amount' => $data['amount'],
            'date_time' => now()
        ]);

        return $result;
    }

    public function getAgentDepositLists($page = 1, $per_page = 10, $with = [])
    {
        $offset = ($page - 1) * $per_page;

        $master = auth('api-master')->user();
        $query = AgentDepositRecord::with($with)->where('action_by', $master->id)
            ->orderBy('date_time', 'desc');

        $totalCount = $query->count();

        $results = $query
            ->skip($offset)
            ->take($per_page)
            ->get();

        $totalPages = (int) ceil($totalCount / $per_page);

        return [
            'data' => $results,
            'meta' => [
                'total' => $totalCount,
                'per_page' => $per_page,
                'current_page' => $page,
                'total_pages' => $totalPages,
            ],
        ];
    }

    public function getAgentWithdrawLists($page = 1, $per_page = 10, $with = [])
    {
        $offset = ($page - 1) * $per_page;

        $master = auth('api-master')->user();
        $query = AgentWithdrawRecord::with($with)->where('action_by', $master->id)
            ->orderBy('date_time', 'desc');

        $totalCount = $query->count();

        $results = $query
            ->skip($offset)
            ->take($per_page)
            ->get();

        $totalPages = (int) ceil($totalCount / $per_page);

        return [
            'data' => $results,
            'meta' => [
                'total' => $totalCount,
                'per_page' => $per_page,
                'current_page' => $page,
                'total_pages' => $totalPages,
            ],
        ];
    }
}
