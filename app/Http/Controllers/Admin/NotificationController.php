<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\Notification\ListingRequest;
use App\Http\Services\Admin\NotificationService;
use Illuminate\Support\Facades\Validator;

class NotificationController extends ApiController
{
    private $notification_service;

    public function __construct(NotificationService $notification_service)
    {
        $this->notification_service = $notification_service;
    }

    public function getDepositNotifications(ListingRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;
            $conditions = ['type' => 'user_deposit', 'recipient_type' => 'admin', 'recipient_id' => auth('api-admin')->user()->id];
            $res_data = $this->notification_service->getDataWithPagination($per_page, $page, conditions: $conditions);
            return $this->paginatedSuccessResponse($res_data, 200, 'Deposit Notification Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getWithdrawNotifications(ListingRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;
            $res_data = $this->notification_service->getWithdrawNotiWithPagination($per_page, $page, auth('api-admin')->user()->id);
            return $this->paginatedSuccessResponse($res_data, 200, 'Withdraw Notification Lists');
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
            $data = $this->notification_service->find($id);
            if ($data) {
                return $this->successResponse($data, 200, 'notification');
            } else {
                return $this->errorResponse('Notification not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function readDepositNotifications()
    {
        try {
            $this->notification_service->readDepositNoti();
            return $this->successResponse([], 200, 'read notifications successfully');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function readWithdrawNotifications()
    {
        try {
            $this->notification_service->readWithdrawNoti();
            return $this->successResponse([], 200, 'read notifications successfully');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}