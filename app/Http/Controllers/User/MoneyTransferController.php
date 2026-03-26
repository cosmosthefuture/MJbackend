<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;


use App\Http\Requests\User\UserMoneyTransfer\FindUserByPhoneNumberRequest;
use App\Http\Requests\User\UserMoneyTransfer\MoneyTransferAcceptRequest;
use App\Http\Requests\User\UserMoneyTransfer\MoneyTransferRejectRequest;
use App\Http\Requests\User\UserMoneyTransfer\UserMoneyTransferCreateRequest;
use App\Models\GlobalCommissionSetting;
use Illuminate\Http\Request;
use App\Http\Services\User\MoneyTransferService;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class MoneyTransferController extends ApiController
{
    private $money_transfer_service;

    public function __construct(MoneyTransferService $money_transfer_service)
    {
        $this->money_transfer_service = $money_transfer_service;
    }

    public function create(UserMoneyTransferCreateRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $auth_user = auth('api-user')->user();
            if (!Hash::check($validated['password'], $auth_user->password)) {
                return $this->errorResponse("Wrong Password. Try again!", 401);
            }
            $data = $this->money_transfer_service->findUser($validated['recipient_id']);
            if(!$data) {
                return $this->errorResponse('User with that phone number does not exist.', 404);
            }

            if($data->id == $auth_user->id) {
                return $this->errorResponse("You cannot transfer money to your account.", 409);
            }
            $money_transfer_percentage = GlobalCommissionSetting::where('key', 'user_money_transfer_house_cut_percentage')->first();
            $house_cut_amount = $validated['amount'] * ($money_transfer_percentage->value/100);
            if($validated['amount'] > $auth_user->balance) {
                return $this->errorResponse("Insufficient Balance.", 409);
            }
            $validated['sender_id'] = $auth_user->id;
            $validated['house_cut_percentage'] = $money_transfer_percentage->value;
            $validated['house_cut_amount'] = $house_cut_amount;
            $result = $this->money_transfer_service->createMoneyTransfer($validated);
            return $this->successResponse($result, 200, 'Transfered money successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function findUserByPhoneNumber(FindUserByPhoneNumberRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $data = $this->money_transfer_service->findUserByPhoneNumber($validated['phone_number']);
            if ($data) {
                return $this->successResponse($data, 200, 'user');
            } else {
                return $this->errorResponse('User with that phone number does not exist.', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function accept(MoneyTransferAcceptRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $record = $this->money_transfer_service->find($validated['record_id']);
            if(!$record) {
                return $this->errorResponse('Record not found.', 404);
            }
            if ($record->recipient_id !== auth('api-user')->user()->id) {
                return $this->errorResponse('This is not your record. You cannot confirm.', 409);
            }
            if($record->status == 'accepted') {
                return $this->errorResponse('Already confirmed.', 409);
            }
            if ($record->status == 'rejected') {
                return $this->errorResponse('Already cancelled.', 409);
            }
            $this->money_transfer_service->accept($record, $validated['notification_id']);
            return $this->successResponse([], 200, 'Confirmed successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function reject(MoneyTransferRejectRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $record = $this->money_transfer_service->find($validated['record_id']);
            if (!$record) {
                return $this->errorResponse('Record not found.', 404);
            }
            if ($record->recipient_id !== auth('api-user')->user()->id) {
                return $this->errorResponse('This is not your record. You cannot cancel.', 409);
            }
            if ($record->status == 'accepted') {
                return $this->errorResponse('Already confirmed.', 409);
            }
            if ($record->status == 'rejected') {
                return $this->errorResponse('Already cancelled.', 409);
            }
            $this->money_transfer_service->reject($record, $validated['notification_id']);
            return $this->successResponse([], 200, 'Cancelled successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}