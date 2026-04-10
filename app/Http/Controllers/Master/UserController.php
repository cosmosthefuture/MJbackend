<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Master\User\AddMoneyToUserRequest;
use App\Http\Requests\Master\User\CreateRequest;
use App\Http\Requests\Master\User\ListingRequest;
use App\Http\Requests\Master\User\ResetUserPasswordRequest;
use App\Http\Requests\Master\User\UpdateRequest;
use App\Http\Requests\Master\User\UserDepositListRequest;
use App\Http\Requests\Master\User\UserWithdrawListRequest;
use App\Http\Requests\Master\User\VerifyUserRequest;
use App\Http\Requests\Master\User\WithdrawMoneyFromUserRequest;
use Illuminate\Http\Request;

use App\Http\Services\Master\UserService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends ApiController
{
    private $user_service;

    public function __construct(UserService $user_service)
    {
        $this->user_service = $user_service;
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
            $conditions = [];
            $status = null;

            if (!empty($validated['search'])) {
                $search = $validated['search'];

                $searches = [
                    'name' => $search,
                    'phone_number' => $search,
                ];

                if (in_array(strtolower($search), ['active', 'inactive'])) {
                    $searches = [];
                    $status = $search;
                }
                if (in_array(strtolower($search), ['verified', 'unverified'])) {
                    $searches = [];
                    if ($search == 'verified') {
                        $conditions['is_verified'] = true;
                    } else {
                        $conditions['is_verified'] = false;
                    }
                }
            }
            $master = auth('api-master')->user();
            $conditions['master_id'] = $master->id;
            $res_data = $this->user_service->getDataWithPagination($per_page, $page, searches: $searches, status: $status, conditions: $conditions);
            return $this->paginatedSuccessResponse($res_data, 200, 'User Lists');
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
            $user = $this->user_service->find($id);
            if ($user) {
                return $this->successResponse($user, 200, 'user');
            } else {
                return $this->errorResponse('User not found', 404);
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
            $result = $this->user_service->createByMaster($validated);
            return $this->successResponse($result, 200, 'User is created successfully');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function verifyUser(VerifyUserRequest $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $user = $this->user_service->find($id);
            if ($user) {
                if ($user->is_verified) {
                    return $this->errorResponse('User is already verified', 409);
                }
                $result = $this->user_service->verifyUser($id, $validated);
                return $this->successResponse($result, 200, 'User is verified successfully');
            } else {
                return $this->errorResponse('User not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function resetUserPassword(ResetUserPasswordRequest $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $user = $this->user_service->find($id);
            if ($user) {
                if (!$user->is_verified) {
                    return $this->errorResponse('User is not verified yet.', 409);
                }
                $result = $this->user_service->resetUserPasswordByMaster($id, $validated);
                return $this->successResponse($result, 200, 'User Password is reset successfully');
            } else {
                return $this->errorResponse('User not found', 404);
            }
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
            $user = $this->user_service->find($id);
            if ($user) {
                if (!$user->is_verified) {
                    return $this->errorResponse('User is not verified yet.', 409);
                }
                $result = $this->user_service->update($id, $validated);
                return $this->successResponse($result, 200, 'User is updated successfully');
            } else {
                return $this->errorResponse('User not found', 404);
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
            $user = $this->user_service->find($id);
            if ($user) {
                $this->user_service->toggleUserStatus($user);
                return $this->successResponse([], 200, 'Toggle status successfully');
            } else {
                return $this->errorResponse('User not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function addMoneyToUser(AddMoneyToUserRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $master = auth('api-master')->user();
            if ($master->balance < $validated['amount']) {
                return $this->errorResponse('Insufficient balance to add money to user', 409);
            }
            $result = $this->user_service->addMoneyToUser($validated);
            return $this->successResponse($result, 200, 'Money is added to User successfully');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function withdrawMoneyFromUser(WithdrawMoneyFromUserRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $user = $this->user_service->whereFirst('id', $validated['user_id']);
            if ($user->balance < $validated['amount']) {
                return $this->errorResponse("withraw amount is greater than user's balance", 409);
            }
            $this->user_service->withdrawMoneyFromUser($validated);
            return $this->successResponse([], 200, 'Money is withdrawed from User successfully');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function userDepositLists(UserDepositListRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;

            $res_data = $this->user_service->getUserDepositLists($per_page, $page, with: ['user', 'actionByMaster']);
            return $this->paginatedSuccessResponse($res_data, 200, 'User Deposit Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function userWithdrawLists(UserWithdrawListRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;

            $res_data = $this->user_service->getUserWithdrawLists($per_page, $page, with: ['user', 'actionByMaster']);
            return $this->paginatedSuccessResponse($res_data, 200, 'User Withdraw Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}