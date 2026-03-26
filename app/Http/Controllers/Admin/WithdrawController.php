<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;


use App\Http\Requests\Admin\Withdraw\AgentWithdrawRequestListingRequest;
use App\Http\Requests\Admin\Withdraw\ManualUserWithdrawCreateRequest;
use App\Http\Requests\Admin\Withdraw\ManualUserWithdrawListRequest;
use App\Http\Requests\Admin\Withdraw\MasterWithdrawRequestListingRequest;
use App\Http\Requests\Admin\Withdraw\RejectAgentWithdrawRequestRequest;
use App\Http\Requests\Admin\Withdraw\RejectMasterWithdrawRequestRequest;
use App\Http\Requests\Admin\Withdraw\RejectUserWithdrawRequestRequest;
use App\Http\Requests\Admin\Withdraw\UserWithdrawRequestListingRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Services\Admin\WithdrawService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class WithdrawController extends ApiController
{
    private $withdraw_service;

    public function __construct(WithdrawService $withdraw_service)
    {
        $this->withdraw_service = $withdraw_service;
    }

    public function index(UserWithdrawRequestListingRequest $request)
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
            $res_data = $this->withdraw_service->getDataWithPagination($per_page, $page, with: $with, whereHas: $whereHas);
            return $this->paginatedSuccessResponse($res_data, 200, 'User Withdraw Request Lists');
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
            $data = $this->withdraw_service->findWithdrawRequest($id);
            if ($data) {
                return $this->successResponse($data, 200, 'user withdraw request');
            } else {
                return $this->errorResponse('Data not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function approveUserWithdrawRequest($id)
    {
        try {
            if (!is_numeric($id)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $data = $this->withdraw_service->findWithdrawRequest($id);
            if (!$data) {
                return $this->errorResponse('Data not found', 404);
            }

            if ($data->status == 'approved') {
                return $this->errorResponse('Already approved', 409);
            }
            if ($data->status == 'rejected') {
                return $this->errorResponse('Already rejected', 409);
            }

            $this->withdraw_service->approveUserWithdrawRequest($data);
            return $this->successResponse([], 200, 'user withdraw request is approved successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function rejectUserWithdrawRequest(RejectUserWithdrawRequestRequest $request, $id)
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
            $data = $this->withdraw_service->findWithdrawRequest($id);
            if (!$data) {
                return $this->errorResponse('Data not found', 404);
            }

            if ($data->status == 'approved') {
                return $this->errorResponse('Already approved', 409);
            }
            if ($data->status == 'rejected') {
                return $this->errorResponse('Already rejected', 409);
            }

            $this->withdraw_service->rejectUserWithdrawRequest($data, $validated['reason_for_rejection']);
            return $this->successResponse([], 200, 'user withdraw request is rejected successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function manualCreateUserWithdraw(ManualUserWithdrawCreateRequest $request)
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
            $user = User::find($validated['user_id']);
            if ($user->balance < $validated['amount']) {
                return $this->errorResponse("You cannot withdraw amount more than remaining balance.", 409);
            }
            $data = $this->withdraw_service->whereLatest('user_id', $validated['user_id']);
            if ($data && $data->status == 'pending') {
                return $this->errorResponse("User's previous withdraw is still pending and cannot withdraw another one for now.", 409);
            }
            $msg = $this->withdraw_service->manualCreateUserWithdraw($validated);
            return $this->successResponse([], 200, $msg);
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getManualWithdrawLists(ManualUserWithdrawListRequest $request)
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
            $res_data = $this->withdraw_service->getManualWithdrawLists($per_page, $page, with: $with, searches: $searches);
            return $this->paginatedSuccessResponse($res_data, 200, 'User Manual Withdraw Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function agentWithdrawRequestList(AgentWithdrawRequestListingRequest $request)
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
            $searches = $validated['search'] ?? null;
            $res_data = $this->withdraw_service->getAgentWithdrawRequestsWithPagination($per_page, $page, with: $with, searches: $searches);
            return $this->paginatedSuccessResponse($res_data, 200, 'Agent Withdraw Request Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function approveAgentWithdrawRequest($id)
    {
        try {
            if (!is_numeric($id)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $data = $this->withdraw_service->findAgentWithdrawRequest($id);
            if (!$data) {
                return $this->errorResponse('Data not found', 404);
            }

            if ($data->status == 'approved') {
                return $this->errorResponse('Already approved', 409);
            }
            if ($data->status == 'rejected') {
                return $this->errorResponse('Already rejected', 409);
            }

            $this->withdraw_service->approveAgentWithdrawRequest($data);
            return $this->successResponse([], 200, 'agent withdraw request is approved successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function rejectAgentWithdrawRequest(RejectAgentWithdrawRequestRequest $request, $id)
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
            $data = $this->withdraw_service->findAgentWithdrawRequest($id);
            if (!$data) {
                return $this->errorResponse('Data not found', 404);
            }

            if ($data->status == 'approved') {
                return $this->errorResponse('Already approved', 409);
            }
            if ($data->status == 'rejected') {
                return $this->errorResponse('Already rejected', 409);
            }

            $this->withdraw_service->rejectAgentWithdrawRequest($data, $validated['reason_for_rejection']);
            return $this->successResponse([], 200, 'agent withdraw request is rejected successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function findAgentWithdrawRequest($id)
    {
        try {
            if (!is_numeric($id)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $data = $this->withdraw_service->findAgentWithdraw($id);
            if ($data) {
                return $this->successResponse($data, 200, 'agent withdraw request');
            } else {
                return $this->errorResponse('Data not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function masterWithdrawRequestList(MasterWithdrawRequestListingRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;
            $with = ['master', 'actionBy', 'paymentMethod'];
            $searches = $validated['search'] ?? null;
            $res_data = $this->withdraw_service->getMasterWithdrawRequestsWithPagination($per_page, $page, with: $with, searches: $searches);
            return $this->paginatedSuccessResponse($res_data, 200, 'Master Withdraw Request Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function approveMasterWithdrawRequest($id)
    {
        try {
            if (!is_numeric($id)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $data = $this->withdraw_service->findMasterWithdrawRequest($id);
            if (!$data) {
                return $this->errorResponse('Data not found', 404);
            }

            if ($data->status == 'approved') {
                return $this->errorResponse('Already approved', 409);
            }
            if ($data->status == 'rejected') {
                return $this->errorResponse('Already rejected', 409);
            }

            $this->withdraw_service->approveMasterWithdrawRequest($data);
            return $this->successResponse([], 200, 'master withdraw request is approved successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function rejectMasterWithdrawRequest(RejectMasterWithdrawRequestRequest $request, $id)
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
            $data = $this->withdraw_service->findMasterWithdrawRequest($id);
            if (!$data) {
                return $this->errorResponse('Data not found', 404);
            }

            if ($data->status == 'approved') {
                return $this->errorResponse('Already approved', 409);
            }
            if ($data->status == 'rejected') {
                return $this->errorResponse('Already rejected', 409);
            }

            $this->withdraw_service->rejectMasterWithdrawRequest($data, $validated['reason_for_rejection']);
            return $this->successResponse([], 200, 'master withdraw request is rejected successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function findMasterWithdrawRequest($id)
    {
        try {
            if (!is_numeric($id)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $data = $this->withdraw_service->findMasterWithdraw($id);
            if ($data) {
                return $this->successResponse($data, 200, 'master withdraw request');
            } else {
                return $this->errorResponse('Data not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}