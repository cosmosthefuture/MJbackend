<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Admin\GlobalCommissionSetting\UpdateRequest;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\GlobalCommissionSetting\ListingRequest;
use App\Http\Services\Admin\GlobalCommissionSettingService;
use Illuminate\Support\Facades\Validator;

class GlobalCommissionSettingController extends ApiController
{
    private $global_commission_setting_service;

    public function __construct(GlobalCommissionSettingService $global_commission_setting_service)
    {
        $this->global_commission_setting_service = $global_commission_setting_service;
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
            $res_data = $this->global_commission_setting_service->getDataWithPagination($per_page, $page);
            return $this->paginatedSuccessResponse($res_data, 200, 'Global Commission Setting Lists');
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
            $data = $this->global_commission_setting_service->find($id);
            if ($data) {
                $result = $this->global_commission_setting_service->update($id, $validated);
                return $this->successResponse($result, 200, 'Value is updated successfully');
            } else {
                return $this->errorResponse('Record not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}