<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Http\Requests\User\Chat\CreateRequest;
use Illuminate\Http\Request;
use App\Http\Requests\User\Chat\ListingRequest;
use App\Http\Services\User\ChatService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ChatController extends ApiController
{
    private $chat_service;

    public function __construct(ChatService $chat_service)
    {
        $this->chat_service = $chat_service;
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
            $conditions = ['game_room_id' => $validated['game_room_id']];
            $res_data = $this->chat_service->getDataWithPagination($per_page, $page, conditions: $conditions, with:['user', 'gameRoom']);
            return $this->paginatedSuccessResponse($res_data, 200, 'Chat Message Lists');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function sendMessage(CreateRequest $request)
    {
        try {
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $gameRoomId = $validated['game_room_id'];
            $game_type = $this->chat_service->get_game_type($gameRoomId);
            $baseUrl = $baseUrl = config('services.web_socket.base_url');
            if($game_type == 'Spin Wheel') {
                $response = Http::withHeaders([
                    'X-Internal-Secret' => config('services.web_socket.internal_secret'),
                ])->get("{$baseUrl}/api/internal/rooms/{$gameRoomId}/spin-wheel/user-ids");
            } else {
                $response = Http::withHeaders([
                    'X-Internal-Secret' => config('services.web_socket.internal_secret'),
                ])->get("{$baseUrl}/api/internal/rooms/{$gameRoomId}/coin-flip/user-ids");
            }
            if ($response['success'] !== 'true') {
                logger()->error("Calling to websocket server fails::");
                return $this->errorResponse('Something went wrong!', 500);
            }
            $user_ids = $response['ids'];
            if (!in_array(auth('api-user')->id(), $user_ids)) {
                return $this->errorResponse("You have not joined the game room yet.", 409);
            }
            $message = $this->chat_service->sendMessage($validated, $game_type);
            if($message == false ) {
                return $this->errorResponse('Something went wrong when sending message!', 500);
            }
            return $this->successResponse($message, 200, 'Sent message successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}