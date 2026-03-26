<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Http\Requests\User\MahJongGameRoom\ListingRequest;
use App\Http\Services\User\MahJongGameRoomService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class MahJongGameRoomController extends ApiController
{
    private $game_room_service;

    public function __construct(MahJongGameRoomService $game_room_service)
    {
        $this->game_room_service = $game_room_service;
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
                    'room_name' => $search,
                ];

                if (in_array(strtolower($search), ['open', 'closed'])) {
                    $searches = [];
                    $status = $search;
                }
            }

            if (!empty($validated['game_rule_id'])) {
                $game_rule_id = $validated['game_rule_id'];

                $conditions['game_rule_id'] = $game_rule_id;
            }
            $res_data = $this->game_room_service->getDataWithPagination($per_page, $page, with: ['game', 'gameRule.fees'], status: $status, searches: $searches, conditions: $conditions);
            return $this->paginatedSuccessResponse($res_data, 200, 'Game Room Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getAllRooms()
    {
        try {
            $res_data = $this->game_room_service->getAll();
            return $this->successResponse($res_data, 200, 'Game Room Lists');
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
            $data = $this->game_room_service->find($id);
            if ($data) {
                return $this->successResponse($data, 200, 'game room');
            } else {
                return $this->errorResponse('Game Room not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}