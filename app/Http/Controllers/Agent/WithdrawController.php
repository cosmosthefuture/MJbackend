<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\ApiController;

use App\Http\Requests\Agent\Withdraw\AgentWithdrawHistoryRequest;
use App\Http\Requests\Agent\Withdraw\WithdrawRequestCreateRequest;

use Illuminate\Http\Request;
use App\Http\Services\Agent\WithdrawService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class WithdrawController extends ApiController
{
    private $withdraw_service;

    public function __construct(WithdrawService $withdraw_service)
    {
        $this->withdraw_service = $withdraw_service;
    }

    public function agentWithdrawRequestList(AgentWithdrawHistoryRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;
            $with = ['agent', 'actionBy', 'paymentMethod'];
            $res_data = $this->withdraw_service->getAgentWithdrawHistoryWithPagination(auth('api-agent')->user()->id, $per_page, $page, with: $with);
            return $this->paginatedSuccessResponse($res_data, 200, 'Agent Withdraw History Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function create(WithdrawRequestCreateRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $agent = auth('api-agent')->user();
            $data = $this->withdraw_service->whereLatest('agent_id', $agent->id);
            if($data && $data->status == 'pending') {
                return $this->errorResponse("Your previous withdraw is still pending and cannot request another one for now.", 409);
            }
            if ($agent->balance < $validated['amount']) {
                return $this->errorResponse("You cannot withdraw amount more than your balance.", 409);
            }
            if (!Hash::check($validated['password'], $agent->password)) {
                return $this->errorResponse("Wrong Password. Try again!", 401);
            }
            $result = $this->withdraw_service->createWithdrawRequest($validated);
            return $this->successResponse($result, 200, 'Your withdraw request has been sent and is waiting for approval.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}