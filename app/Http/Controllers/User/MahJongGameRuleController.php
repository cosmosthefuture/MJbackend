<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Http\Requests\User\MahJongGameRule\ListingRequest;
use App\Http\Services\User\MahJongGameRuleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class MahJongGameRuleController extends ApiController
{
    private $game_rule_service;

    public function __construct(MahJongGameRuleService $game_rule_service)
    {
        $this->game_rule_service = $game_rule_service;
    }

    public function getAllRules()
    {
        try {
            $res_data = $this->game_rule_service->getAll();
            return $this->successResponse($res_data, 200, 'Game Rule Lists');
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
            $data = $this->game_rule_service->find($id);
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
}