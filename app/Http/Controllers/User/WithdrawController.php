<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;

use App\Http\Requests\User\Withdraw\ListingRequest;
use App\Http\Requests\User\Withdraw\WithdrawRequestCreateRequest;

use Illuminate\Http\Request;
use App\Http\Services\User\WithdrawService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tzsk\Otp\Facades\Otp;

class WithdrawController extends ApiController
{
    private $withdraw_service;

    public function __construct(WithdrawService $withdraw_service)
    {
        $this->withdraw_service = $withdraw_service;
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
            $res_data = $this->withdraw_service->getDataWithPagination($per_page, $page, with: $with, conditions: $conditions);
            return $this->paginatedSuccessResponse($res_data, 200, 'Withdraw History Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getManualWithdraws(ListingRequest $request)
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

            $res_data = $this->withdraw_service->getManualWithdraws($per_page, $page, with: $with);
            return $this->paginatedSuccessResponse($res_data, 200, 'Manual Withdraw History Lists');
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
            $user = auth()->user();
            $data = $this->withdraw_service->whereLatest('user_id', $user->id);
            if ($data && $data->status == 'pending') {
                return $this->errorResponse("Your previous withdraw is still pending and cannot request another one for now.", 409);
            }
            if ($user->balance < $validated['amount']) {
                return $this->errorResponse("You cannot withdraw amount more than your balance.", 409);
            }
            if (!Hash::check($validated['password'], $user->password)) {
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