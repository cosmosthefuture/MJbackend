<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Admin\Master\AddMoneyToMasterRequest;
use App\Http\Requests\Admin\Master\MasterDepositListRequest;
use App\Http\Requests\Admin\Master\MasterWithdrawListRequest;
use App\Http\Requests\Admin\Master\WithdrawMoneyFromMasterRequest;
use App\Http\Requests\Master\LoginRequest;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\Master\CreateRequest;
use App\Http\Requests\Admin\Master\UpdateRequest;
use App\Http\Requests\Admin\Master\ListingRequest;
use App\Http\Services\Admin\MasterService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MasterController extends ApiController
{
    private $master_service;

    public function __construct(MasterService $master_service)
    {
        $this->master_service = $master_service;
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
                ];

                if (in_array(strtolower($search), ['active', 'inactive'])) {
                    $searches = [];
                    $status = $search;
                }
            }

            $res_data = $this->master_service->getDataWithPagination($per_page, $page, searches: $searches, status: $status);
            return $this->paginatedSuccessResponse($res_data, 200, 'Master Lists');
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
            $master = $this->master_service->find($id);
            if ($master) {
                return $this->successResponse($master, 200, 'master');
            } else {
                return $this->errorResponse('Master not found', 404);
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
            $result = $this->master_service->create($validated);
            return $this->successResponse($result, 200, 'Master is created successfully');
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
            $master = $this->master_service->find($id);
            if ($master) {
                $result = $this->master_service->update($id, $validated);
                return $this->successResponse($result, 200, 'Master is updated successfully');
            } else {
                return $this->errorResponse('Master not found', 404);
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
            $master = $this->master_service->find($id);
            if ($master) {
                if ($this->master_service->delete($id)) {
                    return $this->successResponse([], 200, 'Master deleted successfully!');
                }
            } else {
                return $this->errorResponse('Master not found!', 404);
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
            $master = $this->master_service->find($id);
            if ($master) {
                $this->master_service->toggleMasterStatus($master);
                return $this->successResponse([], 200, 'Toggle status successfully');
            } else {
                return $this->errorResponse('Master not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function addMoneyToMaster(AddMoneyToMasterRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $result = $this->master_service->addMoneyToMaster($validated);
            return $this->successResponse($result, 200, 'Money is added to Master successfully');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function withdrawMoneyFromMaster(WithdrawMoneyFromMasterRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $master = $this->master_service->whereFirst('id', $validated['master_id']);
            if ($master->balance < $validated['amount']) {
                return $this->errorResponse("withdrawed amount is greater than master's balance", 409);
            }
            $result = $this->master_service->withdrawMoneyFromMaster($validated);
            return $this->successResponse($result, 200, 'Money is withdrawed from Master successfully');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function masterDepositLists(MasterDepositListRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;

            $res_data = $this->master_service->getMasterDepositLists($per_page, $page, with: ['master', 'actionBy']);
            return $this->paginatedSuccessResponse($res_data, 200, 'Master Deposit Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function masterWithdrawLists(MasterWithdrawListRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;

            $res_data = $this->master_service->getMasterWithdrawLists($per_page, $page, with: ['master', 'actionBy']);
            return $this->paginatedSuccessResponse($res_data, 200, 'Master Withdraw Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}
