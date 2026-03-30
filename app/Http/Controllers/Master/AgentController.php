<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Master\Agent\AddMoneyToAgentRequest;
use App\Http\Requests\Master\Agent\AgentDepositListRequest;
use App\Http\Requests\Master\Agent\AgentWithdrawListRequest;
use App\Http\Requests\Master\Agent\WithdrawMoneyFromAgentRequest;
use Illuminate\Http\Request;
use App\Http\Requests\Master\Agent\CreateRequest;
use App\Http\Requests\Master\Agent\UpdateRequest;
use App\Http\Requests\Master\Agent\ListingRequest;
use App\Http\Services\Master\AgentService;
use Illuminate\Support\Facades\Validator;

class AgentController extends ApiController
{
    private $agent_service;

    public function __construct(AgentService $agent_service)
    {
        $this->agent_service = $agent_service;
    }

    public function index(ListingRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;

            $searches = [];
            $status = null;

            if (!empty($validated['search'])) {
                $search = $validated['search'];

                $searches = [
                    'name' => $search,
                    'username' => $search,
                    // 'email' => $search,
                    'phone_number' => $search,
                    'agent_code' => $search,
                ];

                if (in_array(strtolower($search), ['active', 'inactive'])) {
                    $searches = [];
                    $status = $search;
                }
            }

            $conditions = [];
            $conditions['master_id'] = auth('api-master')->user()->id;
            $res_data = $this->agent_service->getDataWithPagination($per_page, $page, searches: $searches, status: $status, with: ['master'], conditions: $conditions);
            return $this->paginatedSuccessResponse($res_data, 200, 'Agent Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function findOrFail($id)
    {
        try {
            if (!is_numeric($id)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $agent = $this->agent_service->find($id);
            if ($agent) {
                return $this->successResponse($agent, 200, 'agent');
            } else {
                return $this->errorResponse('Agent not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function create(CreateRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $result = $this->agent_service->create($validated);
            return $this->successResponse($result, 200, 'Agent is created successfully');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $agent = $this->agent_service->find($id);
            if ($agent) {
                $result = $this->agent_service->update($id, $validated);
                return $this->successResponse($result, 200, 'Agent is updated successfully');
            } else {
                return $this->errorResponse('Agent not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }

    }

    public function delete($id)
    {
        try {
            if (!is_numeric($id)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $agent = $this->agent_service->find($id);
            if ($agent) {
                if ($this->agent_service->delete($id)) {
                    return $this->successResponse([], 200, 'Agent deleted successfully!');
                }
            } else {
                return $this->errorResponse('Agent not found!', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function toggleActive($id)
    {
        try {
            if (!is_numeric($id)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $agent = $this->agent_service->find($id);
            if ($agent) {
                $this->agent_service->toggleAgentStatus($agent);
                return $this->successResponse([], 200, 'Toggle status successfully');
            } else {
                return $this->errorResponse('Agent not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function addMoneyToAgent(AddMoneyToAgentRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $agent = $this->agent_service->find($validated['agent_id']);
            $master = $agent->master;
            if ($master->balance < $validated['amount']) {
                return $this->errorResponse('Insufficient balance to add money to agent', 409);
            }
            $result = $this->agent_service->addMoneyToAgent($validated);
            return $this->successResponse($result, 200, 'Money is added to Agent successfully');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function withdrawMoneyFromAgent(WithdrawMoneyFromAgentRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $agent = $this->agent_service->whereFirst('id', $validated['agent_id']);
            if ($agent->balance < $validated['amount']) {
                return $this->errorResponse("withdrawed amount is greater than agent's balance", 409);
            }
            $result = $this->agent_service->withdrawMoneyFromAgent($validated);
            return $this->successResponse($result, 200, 'Money is withdrawed from Agent successfully');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function agentDepositLists(AgentDepositListRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;

            $res_data = $this->agent_service->getAgentDepositLists($per_page, $page, with: ['agent', 'actionBy']);
            return $this->paginatedSuccessResponse($res_data, 200, 'Agent Deposit Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function agentWithdrawLists(AgentWithdrawListRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;

            $res_data = $this->agent_service->getAgentWithdrawLists($per_page, $page, with: ['agent', 'actionBy']);
            return $this->paginatedSuccessResponse($res_data, 200, 'Agent Withdraw Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}
