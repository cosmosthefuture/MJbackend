<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\PaymentMethod\CreateRequest;
use App\Http\Requests\Admin\PaymentMethod\UpdateRequest;
use App\Http\Requests\Admin\PaymentMethod\ListingRequest;
use App\Http\Services\Admin\PaymentMethodService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends ApiController
{
    private $payment_method_service;

    public function __construct(PaymentMethodService $payment_method_service)
    {
        $this->payment_method_service = $payment_method_service;
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
                    'type' => $search,
                ];

                if (in_array(strtolower($search), ['active', 'inactive'])) {
                    $searches = [];
                    $status = $search;
                }
            }
            $res_data = $this->payment_method_service->getDataWithPagination($per_page, $page, status: $status, searches: $searches);
            return $this->paginatedSuccessResponse($res_data, 200, 'Payment Method Lists');
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
            $data = $this->payment_method_service->find($id);
            if ($data) {
                return $this->successResponse($data, 200, 'payment method');
            } else {
                return $this->errorResponse('Payment Method not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function create(CreateRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(),  $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $result = $this->payment_method_service->create($validated);
            return $this->successResponse($result, 200, 'Payment Method is created successfully');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(),  $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $data = $this->payment_method_service->find($id);
            if ($data) {
                $result = $this->payment_method_service->update($id, $validated);
                return $this->successResponse($result, 200, 'Payment Method is updated successfully');
            } else {
                return $this->errorResponse('Payment Method not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }

    }

    public function delete($id)
    {
        try {
            if (! is_numeric($id)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $data = $this->payment_method_service->find($id);
            if ($data) {
                if ($this->payment_method_service->delete($id)) {
                    return $this->successResponse([], 200, 'Payment Method deleted successfully!');
                }
            } else {
                return $this->errorResponse('Payment Method not found!', 500);
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
            $data = $this->payment_method_service->find($id);
            if ($data) {
                $this->payment_method_service->togglePaymentMethodStatus($data);
                return $this->successResponse([], 200, 'Toggle status successfully');
            } else {
                return $this->errorResponse('Payment Method not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}