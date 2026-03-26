<?php

namespace App\Http\Services\Agent;

use App\Http\Repositories\Agent\WithdrawRepository;

use App\Jobs\SendFcmToMultipleReceiversJob;
use App\Models\AgentWithdrawRequest;
use Exception;
use Illuminate\Support\Facades\Hash;
use Str;

class WithdrawService
{
    protected $withdraw_repository;

    public function __construct(WithdrawRepository $withdraw_repository)
    {
        $this->withdraw_repository = $withdraw_repository;
    }

    public function getAgentWithdrawHistoryWithPagination(
        int $agentId,
        int $perPage = 10,
        int $page = 1,
        string $orderBy = 'created_at',
        array $searches = null,
        array $conditions = [],
        array $orConditions = [],
        array $with = [],
        ?array $whereHas = null,
        ?string $status = null
    ) {
        try {
            $result = $this->withdraw_repository->getWithdrawHistory(agentId: $agentId, page: $page, per_page: $perPage, with: $with);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch agent withdraw history lists with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createWithdrawRequest(array $attributes)
    {
        try {
            $data = [
                'agent_id' => auth('api-agent')->user()->id,
                'payment_method_id' => $attributes['payment_method_id'],
                'amount' => $attributes['amount'],
                'receiver_phone_number' => $attributes['receiver_phone_number'],
                'status' => 'pending',
            ];

            $result = $this->withdraw_repository->create($data);

            $origin_data = AgentWithdrawRequest::find($result->id);
            $adminIds = $this->withdraw_repository->get_admins_for_noti($origin_data);
            $title = 'Agent Withdraw Request';
            $body = $origin_data->agent->name . ' requested a new withdraw.';

            SendFcmToMultipleReceiversJob::dispatch($adminIds, $title, $body);

            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to create agent withdraw request: ' . $e->getMessage());
            throw $e;
        }
    }

    public function whereLatest($column, $value)
    {
        try {
            $result = $this->withdraw_repository->whereLatest($column, $value);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find agent withdraw req with whereLatest: ' . $e->getMessage());
            throw $e;
        }
    }
}
