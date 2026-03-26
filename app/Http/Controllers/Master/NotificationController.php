<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Http\Requests\Master\Notification\ListingRequest;
use App\Http\Services\Master\NotificationService;
use Illuminate\Support\Facades\Validator;

class NotificationController extends ApiController
{
    private $notification_service;

    public function __construct(NotificationService $notification_service)
    {
        $this->notification_service = $notification_service;
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
            $conditions = ['recipient_type' => 'master', 'recipient_id' => auth('api-master')->user()->id];
            $res_data = $this->notification_service->getDataWithPagination($per_page, $page, conditions: $conditions);
            return $this->paginatedSuccessResponse($res_data, 200, 'Notification Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function findOrFail($id)
    {
        try {
            if (! is_numeric($id)) {
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

    public function readAllNotifications()
    {
        try {
            $this->notification_service->read();
            return $this->successResponse([], 200, 'read notifications successfully');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}