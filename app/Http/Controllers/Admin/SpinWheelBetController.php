<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\SpinWheelBet\ListingRequest;
use App\Http\Services\Admin\SpinWheelBetService;
use Illuminate\Support\Facades\Validator;

class SpinWheelBetController extends ApiController
{
    private $spin_wheel_bet_service;

    public function __construct(SpinWheelBetService $spin_wheel_bet_service)
    {
        $this->spin_wheel_bet_service = $spin_wheel_bet_service;
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
            $with = ['round', 'user', 'round.gameRoom', 'round.gameRoom.game', 'round.result'];
            $search = null;
            if(isset($validated['search'])) {
                $search = $validated['search'];
            }
            $whereHas = [
                'round' => function ($q) {
                    $q->where('status', 'finished');
                }
            ];
            if (!empty($search)) {
                $whereHas['user'] = function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('phone_number', 'LIKE', "%{$search}%");
                };
            }
            $res_data = $this->spin_wheel_bet_service->getDataWithPagination($per_page, $page, with: $with, whereHas: $whereHas);
            return $this->paginatedSuccessResponse($res_data, 200, 'Spin Wheel Bet History Lists');
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
            $data = $this->spin_wheel_bet_service->find($id);
            if ($data) {
                return $this->successResponse($data, 200, 'game');
            } else {
                return $this->errorResponse('Game not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}