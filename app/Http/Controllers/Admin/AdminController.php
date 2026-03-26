<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\CreateRequest;
use App\Http\Requests\Admin\UpdateRequest;
use App\Http\Requests\Admin\ListingRequest;
use App\Http\Services\Admin\AdminService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends ApiController
{
    private $admin_service;

    public function __construct(AdminService $admin_service)
    {
        $this->admin_service = $admin_service;
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

            $res_data = $this->admin_service->getDataWithPagination($per_page, $page, searches: $searches, status: $status, with: ['permissions.permissionType.group']);
            return $this->paginatedSuccessResponse($res_data, 200, 'Admin Lists');
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
            $auth = $this->admin_service->find($id);
            if ($auth) {
                return $this->successResponse($auth, 200, 'admin');
            } else {
                return $this->errorResponse('Admin not found', 404);
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
            $result = $this->admin_service->create($validated);
            return $this->successResponse($result, 200, 'Admin is created successfully');
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
            $admin = $this->admin_service->find($id);
            if ($admin) {
                $result = $this->admin_service->update($id, $validated);
                return $this->successResponse($result, 200, 'Admin is updated successfully');
            } else {
                return $this->errorResponse('Admin not found', 404);
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
            $admin = $this->admin_service->find($id);
            if ($admin) {
                if ($this->admin_service->delete($id)) {
                    return $this->successResponse([], 200, 'Admin deleted successfully!');
                }
            } else {
                return $this->errorResponse('Admin not found!', 500);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $validated = $request->validated();
            $admin = $this->admin_service->whereFirst('phone_number', $validated['phone_number']);
            if (!$admin) {
                return $this->errorResponse("Phone number does not exist.", 401);
            }
            if (!Hash::check($validated['password'], $admin->password)) {
                return $this->errorResponse("Wrong Password. Try again!", 401);
            }

            $accessToken = $this->admin_service->generateAccessToken($admin);
            // $refreshToken = $this->admin_service->generateRefreshToken($admin);

            if (isset($validated['fcm_token'])) {
                $this->admin_service->storeFcmToken($admin, $validated['fcm_token']);
            }

            $permissions = $admin->permissions->map(function ($permission) {
                return [
                    'permission_type_id' => $permission->permission_type_id,
                    'permission_type_name' => $permission->permissionType->name ?? null,
                ];
            });
            $_admin = $this->admin_service->whereFirst('id', $admin->id);
            return $this->successResponse([
                'token_type' => 'bearer',
                'accessToken' => $accessToken,
                'user' => $_admin,
                'type' => 'admin',
                'permission_list' => $permissions
                // 'refreshToken' => $refreshToken,
            ], 200, 'Admin account Logged in Successfully');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function logout()
    {
        try {
            $this->admin_service->logout();
            return $this->successResponse([], 200, 'Successfully logged out');
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
            $admin = $this->admin_service->find($id);
            if ($admin) {
                $this->admin_service->toggleAdminStatus($admin);
                return $this->successResponse([], 200, 'Toggle status successfully');
            } else {
                return $this->errorResponse('Admin not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}
