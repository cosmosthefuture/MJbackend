<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Services\Admin\GameService;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\MahJongGameRule\CreateRequest;
use App\Http\Requests\Admin\MahJongGameRule\UpdateRequest;
use App\Http\Requests\Admin\MahJongGameRule\ListingRequest;
use App\Http\Services\Admin\MahJongGameRuleService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MahJongGameRuleController extends ApiController
{
    private $mah_jong_game_rule_service;
    private $game_service;

    public function __construct(MahJongGameRuleService $mah_jong_game_rule_service, GameService $game_service)
    {
        $this->mah_jong_game_rule_service = $mah_jong_game_rule_service;
        $this->game_service = $game_service;
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
                    'rule_name' => $search,
                ];

                if (in_array(strtolower($search), ['active', 'inactive'])) {
                    $searches = [];
                    $status = $search;
                }
            }
            $res_data = $this->mah_jong_game_rule_service->getDataWithPagination($per_page, $page, with: ['game', 'createdBy', 'updatedBy'], status: $status, searches: $searches);
            return $this->paginatedSuccessResponse($res_data, 200, 'Game Rule Lists');
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
            $data = $this->mah_jong_game_rule_service->find($id);
            if ($data) {
                return $this->successResponse($data, 200, 'game rule');
            } else {
                return $this->errorResponse('Game Rule not found', 404);
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
            $result = $this->mah_jong_game_rule_service->create($validated);
            return $this->successResponse($result, 200, 'Game Rule is created successfully');
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
            $data = $this->mah_jong_game_rule_service->find($id);
            if ($data) {
                $result = $this->mah_jong_game_rule_service->update($id, $validated);
                return $this->successResponse($result, 200, 'Game Rule is updated successfully');
            } else {
                return $this->errorResponse('Game Rule not found', 404);
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
            $data = $this->mah_jong_game_rule_service->find($id);
            if ($data) {
                if ($this->mah_jong_game_rule_service->delete($id)) {
                    return $this->successResponse([], 200, 'Game Rule deleted successfully!');
                }
            } else {
                return $this->errorResponse('Game Rule not found!', 500);
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
            $data = $this->mah_jong_game_rule_service->find($id);
            if ($data) {
                $this->mah_jong_game_rule_service->toggleGameRuleStatus($data);
                return $this->successResponse([], 200, 'Toggle status successfully');
            } else {
                return $this->errorResponse('Game Rule not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getRulesByGameType(ListingRequest $request, $gameTypeId)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $per_page = array_key_exists('per_page', $validated) ? $validated['per_page'] : 20;
            $page = array_key_exists('page', $validated) ? $validated['page'] : 1;
            $game = $this->game_service->find($gameTypeId);
            if(!$game) {
                return $this->errorResponse('Game Type not found', 404);
            }
            $conditions = [];
            $conditions = ['game_id' => $gameTypeId];
            $status = "active";

            $res_data = $this->mah_jong_game_rule_service->getDataWithPagination($per_page, $page, status: $status, conditions: $conditions);
            return $this->paginatedSuccessResponse($res_data, 200, 'Game Rule Lists By Game Type');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}