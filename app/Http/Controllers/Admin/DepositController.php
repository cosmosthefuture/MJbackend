<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;


use App\Http\Requests\Admin\Deposit\ManualUserDepositCreateRequest;
use App\Http\Requests\Admin\Deposit\ManualUserDepositListRequest;
use App\Http\Requests\Admin\Deposit\RejectUserDepositRequestRequest;
use App\Http\Requests\Admin\Deposit\UserDepositRequestListingRequest;
use Illuminate\Http\Request;
use App\Http\Services\Admin\DepositService;
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

    public function index(UserDepositRequestListingRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;
            $with = ['user', 'actionBy', 'paymentMethod'];
            $whereHas = null;

            if (!empty($validated['search'])) {
                $search = $validated['search'];

                $whereHas = [
                    'user' => function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('phone_number', 'LIKE', "%{$search}%");
                    }
                ];
            }
            $res_data = $this->deposit_service->getDataWithPagination($per_page, $page, with: $with, whereHas: $whereHas);
            return $this->paginatedSuccessResponse($res_data, 200, 'User Deposit Request Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getManualDepositLists(ManualUserDepositListRequest $request)
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
            $searches = $validated['search'] ?? null;
            $res_data = $this->deposit_service->getManualDepositLists($per_page, $page, with: $with, searches: $searches);
            return $this->paginatedSuccessResponse($res_data, 200, 'User Manual Deposit Lists');
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
            $data = $this->deposit_service->findDepositRequest($id);
            if ($data) {
                return $this->successResponse($data, 200, 'user deposit request');
            } else {
                return $this->errorResponse('Data not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function approveUserDepositRequest($id)
    {
        try {
            if (!is_numeric($id)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $data = $this->deposit_service->findDepositRequest($id);
            if (!$data) {
                return $this->errorResponse('Data not found', 404);
            }

            if ($data->status == 'approved') {
                return $this->errorResponse('Already approved', 409);
            }
            if ($data->status == 'rejected') {
                return $this->errorResponse('Already rejected', 409);
            }

            $this->deposit_service->approveUserDepositRequest($data);
            return $this->successResponse([], 200, 'user deposit request is approved successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function rejectUserDepositRequest(RejectUserDepositRequestRequest $request, $id)
    {
        try {
            if (!is_numeric($id)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $data = $this->deposit_service->findDepositRequest($id);
            if (!$data) {
                return $this->errorResponse('Data not found', 404);
            }

            if ($data->status == 'approved') {
                return $this->errorResponse('Already approved', 409);
            }
            if ($data->status == 'rejected') {
                return $this->errorResponse('Already rejected', 409);
            }

            $this->deposit_service->rejectUserDepositRequest($data, $validated['reason_for_rejection']);
            return $this->successResponse([], 200, 'user deposit request is rejected successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function manualCreateUserDeposit(ManualUserDepositCreateRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            if (!Hash::check($validated['password'], auth()->user()->password)) {
                return $this->errorResponse("Wrong Password. Try again!", 401);
            }
            $data = $this->deposit_service->whereLatest('user_id', $validated['user_id']);
            if ($data && $data->status == 'pending') {
                return $this->errorResponse("User's previous deposit is still pending and cannot add another one for now.", 409);
            }
            $msg = $this->deposit_service->manualCreateUserDeposit($validated);
            return $this->successResponse([], 200, $msg);
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}