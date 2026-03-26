<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Http\Requests\User\Game\ListingRequest;
use App\Http\Services\User\WebSocketService;
use Illuminate\Support\Facades\Validator;

class WebSocketController extends ApiController
{
    private $web_socket_service;

    public function __construct(WebSocketService $web_socket_service)
    {
        $this->web_socket_service = $web_socket_service;
    }

    public function generateJwtTokenForWS()
    {
        try {
            $user = auth('api-user')->user();
            $result = $this->web_socket_service->generateToken($user);
            $result['user'] = $user;
            return $this->successResponse($result, 200, 'Jwt token generated successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}