<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Agent\AgentDailyWalletSummaryRequest;
use App\Http\Requests\Agent\AgentIncentiveTransactionListRequest;
use App\Http\Requests\Agent\AgentMonthlyIncentiveSummaryRequest;
use App\Http\Requests\Agent\LoginRequest;
use Illuminate\Http\Request;
use App\Http\Services\Agent\AgentService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class AgentController extends ApiController
{
    private $agent_service;

    public function __construct(AgentService $agent_service)
    {
        $this->agent_service = $agent_service;
    }

    public function login(LoginRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $validated = $request->validated();
            $agent = $this->agent_service->whereFirst('phone_number', $validated['phone_number']);
            if (!$agent) {
                return $this->errorResponse("Phone number does not exist.", 401);
            }
            if (!Hash::check($validated['password'], $agent->password)) {
                return $this->errorResponse("Wrong Password. Try again!", 401);
            }

            $accessToken = $this->agent_service->generateAccessToken($agent);
            // $refreshToken = $this->agent_service->generateRefreshToken($admin);

            if(isset($validated['fcm_token'])) {
                $this->agent_service->storeFcmToken($agent, $validated['fcm_token']);
            }

            return $this->successResponse([
                'token_type' => 'bearer',
                'accessToken' => $accessToken,
                // 'refreshToken' => $refreshToken,
                'user' => $agent,
                'type' => 'agent',
                'permission_list' => null
            ], 200, 'Agent account Logged in Successfully');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function logout()
    {
        try {
            $this->agent_service->logout();
            return $this->successResponse([], 200, 'Successfully logged out');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getAgentIncentiveTransactions(AgentIncentiveTransactionListRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;

            $res_data = $this->agent_service->getIncentiveTransactions($page, $per_page, auth('api-agent')->user()->id);
            return $this->paginatedSuccessResponse($res_data, 200, 'Agent Incentive Transaction Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getAgentDailyWalletSummary(AgentDailyWalletSummaryRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;

            $res_data = $this->agent_service->getDailyWalletSummary($page, $per_page, auth('api-agent')->user()->id);
            return $this->paginatedSuccessResponse($res_data, 200, 'Agent Daily Wallet Summary Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getAgentMonthlyIncentiveSummary(AgentMonthlyIncentiveSummaryRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 12;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;

            $res_data = $this->agent_service->getMonthlyIncentiveSummary($page, $per_page, auth('api-agent')->user()->id);
            return $this->paginatedSuccessResponse($res_data, 200, 'Agent Monthly Incentive Summary Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getWalletBalance()
    {
        try {
            $user = auth('api-agent')->user();
            $balance = $user->balance;
            return $this->successResponse(['wallet-balance' => $balance], 200, "wallet balance");
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}