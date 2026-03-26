<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\GameRoom\CreateRequest;
use App\Http\Requests\Admin\GameRoom\UpdateRequest;
use App\Http\Requests\Admin\GameRoom\ListingRequest;
use App\Http\Services\Admin\GameRoomService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class GameRoomController extends ApiController
{
    private $game_room_service;

    public function __construct(GameRoomService $game_room_service)
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

            if (!empty($validated['game_id'])) {
                $game_id = $validated['game_id'];

                $conditions['game_id'] = $game_id;
            }
            if (!empty($validated['game_rule_id'])) {
                $game_rule_id = $validated['game_rule_id'];

                $conditions['game_rule_id'] = $game_rule_id;
            }

            $res_data = $this->game_room_service->getDataWithPagination($per_page, $page, with: ['game', 'createdBy', 'gameRule'], status: $status, searches: $searches, conditions:$conditions);
            return $this->paginatedSuccessResponse($res_data, 200, 'Game Room Lists');
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

    public function create(CreateRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(),  $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $result = $this->game_room_service->create($validated);
            return $this->successResponse($result, 200, 'Game Room is created successfully');
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
            $data = $this->game_room_service->find($id);
            if ($data) {
                $result = $this->game_room_service->update($id, $validated);
                return $this->successResponse($result, 200, 'Game Room is updated successfully');
            } else {
                return $this->errorResponse('Game Room not found', 404);
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
            $data = $this->game_room_service->find($id);
            if ($data) {
                if ($this->game_room_service->delete($id)) {
                    return $this->successResponse([], 200, 'Game Room deleted successfully!');
                }
            } else {
                return $this->errorResponse('Game Room not found!', 500);
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
            $data = $this->game_room_service->find($id);
            if ($data) {
                $this->game_room_service->toggleGameRoomStatus($data);
                return $this->successResponse([], 200, 'Toggle status successfully');
            } else {
                return $this->errorResponse('Game Room not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}