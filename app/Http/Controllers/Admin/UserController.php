<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Requests\Admin\User\CreateRequest;
use App\Http\Requests\Admin\User\ListingRequest;
use App\Http\Requests\Admin\User\ResetUserPasswordRequest;
use App\Http\Requests\Admin\User\UpdateRequest;
use App\Http\Requests\Admin\User\VerifyUserRequest;
use Illuminate\Http\Request;

use App\Http\Services\Admin\UserService;
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
                        $conditions = ['is_verified' => true];
                    } else {
                        $conditions = ['is_verified' => false];
                    }
                }
            }

            $res_data = $this->user_service->getDataWithPagination($per_page, $page, searches: $searches, status: $status, with: ['agent', 'master'], conditions: $conditions);
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
            $result = $this->user_service->createByAdmin($validated);
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
                $result = $this->user_service->resetUserPasswordByAdmin($id, $validated);
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
}