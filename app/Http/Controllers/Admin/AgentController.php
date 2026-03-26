<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;

use Illuminate\Http\Request;
use App\Http\Requests\Admin\Agent\CreateRequest;
use App\Http\Requests\Admin\Agent\UpdateRequest;
use App\Http\Requests\Admin\Agent\ListingRequest;
use App\Http\Services\Admin\AgentService;
use Illuminate\Support\Facades\Validator;

class AgentController extends ApiController
{
    private $agent_service;

    public function __construct(AgentService $agent_service)
    {
        $this->agent_service = $agent_service;
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
                    'username' => $search,
                    // 'email' => $search,
                    'phone_number' => $search,
                ];

                if (in_array(strtolower($search), ['active', 'inactive'])) {
                    $searches = [];
                    $status = $search;
                }
            }

            $res_data = $this->agent_service->getDataWithPagination($per_page, $page, searches: $searches, status: $status);
            return $this->paginatedSuccessResponse($res_data, 200, 'Agent Lists');
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
            $agent = $this->agent_service->find($id);
            if ($agent) {
                return $this->successResponse($agent, 200, 'agent');
            } else {
                return $this->errorResponse('Agent not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}
