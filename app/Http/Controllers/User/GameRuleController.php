<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Http\Requests\User\GameRule\ListingRequest;
use App\Http\Services\User\GameRuleService;
use Illuminate\Support\Facades\Validator;

class GameRuleController extends ApiController
{
    private $game_rule_service;

    public function __construct(GameRuleService $game_rule_service)
    {
        $this->game_rule_service = $game_rule_service;
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
            $conditions = [];

            if (!empty($validated['game_id'])) {
                $game_id = $validated['game_id'];

                $conditions['game_id'] = $game_id;
            }

            $res_data = $this->game_rule_service->getDataWithPaginationCached($per_page, $page, conditions: $conditions);
            return $this->paginatedSuccessResponse($res_data, 200, 'Game Rule Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}