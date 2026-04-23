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

            if (!empty($validated['mah_jong_game_rule_id'])) {
                $mah_jong_game_rule_id = $validated['mah_jong_game_rule_id'];

                $conditions['mah_jong_game_rule_id'] = $mah_jong_game_rule_id;
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

    public function joinRoom($roomId)
    {
        try {
            if (!is_numeric($roomId)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }

            $room = $this->game_room_service->find($roomId);

            if (!$room) {
                return $this->errorResponse('Game Room not found', 404);
            }

            $player_count = $this->game_room_service->get_player_count($roomId);

            $rule = $room->gameRule;

            if (!$rule) {
                return $this->errorResponse('Game Rule not found', 404);
            }

            if ($player_count >= $rule->max_player) {
                return $this->errorResponse('Room is full. Join again later', 409);
            }

            $current_match = $this->game_room_service->getCurrentMatch($roomId);
            if ($current_match) {

            } else {
                $new_match = $this->game_room_service->createNewMatch($roomId, $rule);
                // $betAmount = $rule->bet_amount;

                // $totalFee = $rule->fees->sum('amount'); // assuming column name = fee_amount

                // $totalRequired = $betAmount + $totalFee;

                // $user = auth('api-user')->user();
                // $userBalance = $user->balance;

                // if ($userBalance < $totalRequired) {
                //     return $this->errorResponse(
                //         "Insufficient balance. Required: {$totalRequired}, Your balance: {$userBalance}",
                //         422
                //     );
                // }
            }
            $this->game_room_service->addUserIntoRoomPlayers($roomId, auth('api-user')->user()->id);
            $token = $this->game_room_service->generateJwtTokenToJoinRoom(auth('api-user')->user(), $roomId);
            return $this->successResponse(['token' => $token], 200, 'joined room successfully.');

        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getJoinToken($roomId)
    {
        try {
            if (!is_numeric($roomId)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }

            $room = $this->game_room_service->find($roomId);

            if (!$room) {
                return $this->errorResponse('Game Room not found', 404);
            }

            $token = $this->game_room_service->generateJwtTokenToJoinRoom(auth('api-user')->user(), $roomId);
            return $this->successResponse(['token' => $token], 200, 'join token');

        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function startRound($roomId)
    {
        try {
            if (!is_numeric($roomId)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }

            $room = $this->game_room_service->find($roomId);

            if (!$room) {
                return $this->errorResponse('Game Room not found', 404);
            }

            $round = $this->game_room_service->findCurrentRound($roomId);

            if ($round) {
                return $this->errorResponse('Previous round has not finished yet.', 409);
            }

            $result = $this->game_room_service->createNewRound($roomId);
            $players = $this->game_room_service->assign_seat_positions($room, $result);
            return $this->successResponse(['round_id' => $result->id, 'players' => $players], 200, 'round created successfully.');

        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getRoomData($id)
    {
        try {
            if (!is_numeric($id)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $data = $this->game_room_service->find($id);
            if ($data) {
                $return_data = [
                    'room_name' => $data->room_name,
                    'room_code' => $data->room_code,
                    'round_qty_per_match' => $data->gameRule->round_qty_per_match,
                    'max_player' => $data->gameRule->max_player
                ];
                return $this->successResponse($return_data, 200, 'game room');
            } else {
                return $this->errorResponse('Game Room not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function getCurrentMatch($roomId)
    {
        try {
            if (!is_numeric($roomId)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $data = $this->game_room_service->getCurrentMatch($roomId);
            if ($data) {
                $return_data = [
                    'match_id' => $data->id,
                    'status' => $data->status,
                    'total_rounds' => $data->total_rounds,
                    'current_round_id' => null
                ];
                return $this->successResponse($return_data, 200, 'game room match');
            } else {
                return $this->errorResponse('Game Room Match not found', 404);
            }
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function leaveRoom(Request $request, $roomId)
    {
        try {
            $validated = $request->validate([
                'user_id' => ['required', 'integer', 'exists:users,id'],
            ]);

            if (!is_numeric($roomId)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }

            $room = $this->game_room_service->find($roomId);

            if (!$room) {
                return $this->errorResponse('Game Room not found', 404);
            }

            $this->game_room_service->updateStatusOfLeaveUser(
                $roomId,
                $validated['user_id']
            );

            return $this->successResponse([], 200, 'leave user status updated successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse($e->errors(), 422);

        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function updateRoundPlayerActiveStatus(Request $request, $roundId)
    {
        try {
            $validated = $request->validate([
                'user_id' => ['required', 'integer', 'exists:users,id'],
            ]);

            if (!is_numeric($roundId)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }

            $round = $this->game_room_service->findRound($roundId);

            if (!$round) {
                return $this->errorResponse('Game Round not found', 404);
            }

            $this->game_room_service->updateActiveStatusOfRejoinUser(
                $roundId,
                $validated['user_id']
            );

            return $this->successResponse([], 200, 'rejoined user status updated successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse($e->errors(), 422);

        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function endRound($roundId)
    {
        try {
            if (!is_numeric($roundId)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }

            $round = $this->game_room_service->findRound($roundId);

            if (!$round) {
                return $this->errorResponse('Game Round not found', 404);
            }

            $this->game_room_service->endRound(
                $round,
            );

            return $this->successResponse([], 200, 'rejoined user status updated successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}