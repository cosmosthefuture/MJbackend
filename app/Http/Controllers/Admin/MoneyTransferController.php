<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;


use App\Http\Requests\Admin\UserMoneyTransfer\ListingRequest;
use Illuminate\Http\Request;
use App\Http\Services\Admin\MoneyTransferService;

use Illuminate\Support\Facades\Validator;


class MoneyTransferController extends ApiController
{
    private $money_transfer_service;

    public function __construct(MoneyTransferService $money_transfer_service)
    {
        $this->money_transfer_service = $money_transfer_service;
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

            $res_data = $this->money_transfer_service->getDataWithPagination($per_page, $page, with: ['sender', 'recipient']);
            return $this->paginatedSuccessResponse($res_data, 200, 'User Money Transfer History Lists');
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
            $data = $this->money_transfer_service->find($id);
            if ($data) {
                return $this->successResponse($data, 200, 'data');
            } else {
                return $this->errorResponse('Data not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}