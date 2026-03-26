<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;

use App\Http\Requests\User\Deposit\DepositRequestCreateRequest;

use App\Http\Requests\User\Deposit\ListingRequest;
use Illuminate\Http\Request;
use App\Http\Services\User\DepositService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tzsk\Otp\Facades\Otp;

class DepositController extends ApiController
{
    private $deposit_service;

    public function __construct(DepositService $deposit_service)
    {
        $this->deposit_service = $deposit_service;
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
            $with = ['user', 'paymentMethod', 'actionBy'];
            $conditions = [];
            $conditions = ['user_id' => auth('api-user')->user()->id];
            $res_data = $this->deposit_service->getDataWithPagination($per_page, $page, with: $with, conditions: $conditions);
            return $this->paginatedSuccessResponse($res_data, 200, 'Deposit History Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getManualDeposits(ListingRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;
            $with = ['user', 'actionBy'];

            $res_data = $this->deposit_service->getManualDeposits($per_page, $page, with: $with);
            return $this->paginatedSuccessResponse($res_data, 200, 'Manual Deposit History Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function create(DepositRequestCreateRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $data = $this->deposit_service->whereLatest('user_id', auth()->user()->id);
            if ($data && $data->status == 'pending') {
                return $this->errorResponse("Your previous deposit is still pending and cannot add another one for now.", 409);
            }
            $result = $this->deposit_service->createDepositRequest($validated);
            return $this->successResponse($result, 200, 'Your deposit request has been sent and is waiting for approval.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}