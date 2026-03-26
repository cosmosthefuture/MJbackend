<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Master\LoginRequest;
use App\Http\Requests\Master\MasterDailyWalletSummaryRequest;
use App\Http\Requests\Master\MasterMonthlyIncentiveReportRequest;
use Illuminate\Http\Request;
use App\Http\Services\Master\MasterService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class MasterController extends ApiController
{
    private $master_service;

    public function __construct(MasterService $master_service)
    {
        $this->master_service = $master_service;
    }

    public function login(LoginRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $validated = $request->validated();
            $master = $this->master_service->whereFirst('phone_number', $validated['phone_number']);
            if (!$master) {
                return $this->errorResponse("Phone number does not exist.", 401);
            }
            if (!Hash::check($validated['password'], $master->password)) {
                return $this->errorResponse("Wrong Password. Try again!", 401);
            }

            $accessToken = $this->master_service->generateAccessToken($master);
            // $refreshToken = $this->master_service->generateRefreshToken($admin);

            return $this->successResponse([
                'token_type' => 'bearer',
                'accessToken' => $accessToken,
                // 'refreshToken' => $refreshToken,
                'user' => $master,
                'type' => 'master',
                'permission_list' => null
            ], 200, 'Master account Logged in Successfully');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function logout()
    {
        try {
            $this->master_service->logout();
            return $this->successResponse([], 200, 'Successfully logged out');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getMasterDailyWalletSummary(MasterDailyWalletSummaryRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;

            $res_data = $this->master_service->getDailyWalletSummary($page, $per_page, auth('api-master')->user()->id);
            return $this->paginatedSuccessResponse($res_data, 200, 'Master Daily Wallet Summary Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getMasterMonthlyIncentiveReport(MasterMonthlyIncentiveReportRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;

            $res_data = $this->master_service->getMonthlyIncentiveReport($page, $per_page, auth('api-master')->user()->id);
            return $this->paginatedSuccessResponse($res_data, 200, 'Monthly Incentive Report');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getWalletBalance()
    {
        try {
            $user = auth('api-master')->user();
            $balance = $user->balance;
            return $this->successResponse(['wallet-balance' => $balance], 200, "wallet balance");
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}