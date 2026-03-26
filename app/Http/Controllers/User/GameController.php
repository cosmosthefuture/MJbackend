<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Http\Requests\User\Game\ListingRequest;
use App\Http\Services\User\GameService;
use Illuminate\Support\Facades\Validator;

class GameController extends ApiController
{
    private $game_service;

    public function __construct(GameService $game_service)
    {
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
                    'name' => $search,
                ];

                if (in_array(strtolower($search), ['active', 'inactive'])) {
                    $searches = [];
                    $status = $search;
                }
            }
            $res_data = $this->game_service->getDataWithPaginationCached($per_page, $page, status: $status, searches: $searches);
            return $this->paginatedSuccessResponse($res_data, 200, 'Game Lists');
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
            $data = $this->game_service->find($id);
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