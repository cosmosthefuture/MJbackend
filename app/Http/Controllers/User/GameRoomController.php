<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Http\Requests\User\GameRoom\CoinFlipPlaceBetRequest;
use App\Http\Requests\User\GameRoom\SpinWheelPlaceBetRequest;
use Illuminate\Http\Request;
use App\Http\Requests\User\GameRoom\ListingRequest;
use App\Http\Services\User\GameRoomService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
            $res_data = $this->game_room_service->getDataWithPaginationCached($per_page, $page, with: ['game', 'gameRule'], status: $status, searches: $searches, conditions: $conditions);
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

    public function enterSpinWheelGameRoom($gameRoomId)
    {
        try {
            if (!is_numeric($gameRoomId)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $game_room = $this->game_room_service->whereFirst('id', $gameRoomId);
            if (!$game_room) {
                return $this->errorResponse('Game Room not found', 404);
            }
            $user = auth('api-user')->user();
            $user_balance = $user->balance;
            $min_bet = $game_room->gameRule->min_bet_amount;
            if ($user_balance < $min_bet) {
                return $this->errorResponse('Your balance is lower than the minimum bet for this game room.', 409);
            }
            $baseUrl = $baseUrl = config('services.web_socket.base_url');
            $response = Http::withHeaders([
                'X-Internal-Secret' => config('services.web_socket.internal_secret'),
            ])->get("{$baseUrl}/api/internal/rooms/{$gameRoomId}/spin-wheel/user-count");

            if (!$response['success']) {
                logger()->error("Calling to websocket server fails::");
                return $this->errorResponse('Something went wrong!', 500);
            }
            if ($response['userCount'] == $game_room->gameRule->user_limit ?? 0) {
                return $this->errorResponse('Game room is full. Please join again later.', 409);
            }

            return $this->successResponse(['room_id' => $game_room->id], 200, 'Entered the room successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function startSpinWheelGameRound($gameRoomId)
    {
        try {
            if (!is_numeric($gameRoomId)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $game_room = $this->game_room_service->whereFirst('id', $gameRoomId);
            if (!$game_room) {
                return $this->errorResponse('Game Room not found', 404);
            }

            $running_round_exist = $this->game_room_service->checkIfActiveRoundExistOrNotForSpinWheel($gameRoomId);
            if ($running_round_exist['exist']) {
                return $this->errorResponse('Previous round is not finished yet.', 409);
            }
            $new_round = $this->game_room_service->createNewRoundForSpinWheel($gameRoomId);
            $new_round->load(['gameRoom.game', 'gameRoom.gameRule', 'bets.user']);
            return $this->successResponse($new_round, 200, 'Round created successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function placeBetForSpinWheel(SpinWheelPlaceBetRequest $request, $round_id)
    {
        DB::beginTransaction();
        try {
            if (!is_numeric($round_id)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $game_round = $this->game_room_service->fetchSpinWheelGameRound('id', $round_id);
            if (!$game_round) {
                return $this->errorResponse('Game Round not found', 404);
            }
            if ($game_round->status == "spinning") {
                return $this->errorResponse('Betting is locked for this round.', 409);
            }
            if ($game_round->status == "finished") {
                return $this->errorResponse('This round is already finished.', 409);
            }

            $user = auth('api-user')->user();
            $already_bet = $this->game_room_service->checkIfAlreadyBetorNotForSpinWheel($game_round->id, $user->id);
            $max_bet = $game_round->gameRoom->gameRule->max_bet_amount;
            $min_bet = $game_round->gameRoom->gameRule->min_bet_amount;
            if (!$already_bet) {
                if ($validated['bet_amount'] > $max_bet) {
                    return $this->errorResponse('Your bet amount is higher than the max bet amount.', 409);
                }
                if ($validated['bet_amount'] < $min_bet) {
                    return $this->errorResponse('Your bet amount is lower than the min bet amount.', 409);
                }
            } else {
                $total_bet = $this->game_room_service->getTotalBetAmountOfTargetUserForSpinWheel($game_round->id, $user->id);
                $new_total_bet = $total_bet + $validated['bet_amount'];
                if ($new_total_bet > $max_bet) {
                    return $this->errorResponse('Your total bet amount is higher than the max bet amount.', 409);
                }
            }
            $gameRoomId = $game_round->gameRoom->id;
            $user_balance = $user->balance;

            if ($validated['bet_amount'] > $user_balance) {
                return $this->errorResponse('Insufficient balance.', 409);
            }

            $data = [
                'spin_wheel_round_id' => $game_round->id,
                'user_id' => $user->id,
                'bet_amount' => $validated['bet_amount'],
                'total_winning_chance_percentage' => 0,
                'single_bet_winning_percentage' => 0,
            ];

            $user->withdraw($validated['bet_amount'], ['type' => 'spin_wheel_bet', 'round_id' => $game_round->id]);
            $bet = $this->game_room_service->createBetForSpinWheel($data, $already_bet);
            $bet_info = $this->game_room_service->calculateBetInfoForSpinWheel($game_round->id);

            $baseUrl = $baseUrl = config('services.web_socket.base_url');
            $response = Http::withHeaders([
                'X-Internal-Secret' => config('services.web_socket.internal_secret'),
                'Accept' => 'application/json',
            ])->withBody(json_encode([
                            'bets' => $bet_info,
                            'current_bet_user_id' => $user->id,
                        ]), 'application/json')->post("{$baseUrl}/api/internal/rooms/{$gameRoomId}/spin-wheel/place-bet");

            if (!$response['success']) {
                logger()->error("Calling to websocket server fails::");
                return $this->errorResponse('Something went wrong!', 500);
            }
            DB::commit();
            return $this->successResponse([], 200, 'Placed bet successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function cancelBetForSpinWheel($roundId)
    {
        try {
            if (!is_numeric($roundId)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $game_round = $this->game_room_service->fetchSpinWheelGameRound('id', $roundId);
            if (!$game_round) {
                return $this->errorResponse('Game Round not found', 404);
            }
            if ($game_round->status !== 'betting') {
                return $this->errorResponse('Betting phase is done.Cannot cancel.', 409);
            }
            $hasBet = $game_round->bets()
                ->where('user_id', auth('api-user')->user()->id)
                ->exists();

            if (!$hasBet) {
                return $this->errorResponse('You have not placed a bet in this round.', 404);
            }
            $this->game_room_service->cancelBetForSpinWheel($game_round);
            $gameRoomId = $game_round->gameRoom->id;
            $bet_info = $this->game_room_service->calculateBetInfoForSpinWheel($game_round->id);

            $user = auth('api-user')->user();
            $baseUrl = $baseUrl = config('services.web_socket.base_url');
            $response = Http::withHeaders([
                'X-Internal-Secret' => config('services.web_socket.internal_secret'),
                'Accept' => 'application/json',
            ])->withBody(json_encode([
                            'bets' => $bet_info,
                            'current_bet_user_id' => $user->id,
                        ]), 'application/json')->post("{$baseUrl}/api/internal/rooms/{$gameRoomId}/spin-wheel/cancel-bet");

            if (!$response['success']) {
                logger()->error("Calling to websocket server fails::");
                return $this->errorResponse('Something went wrong!', 500);
            }
            return $this->successResponse([], 200, 'Bet cancelled successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function requestResultForSpinWheel($round_id)
    {
        DB::beginTransaction();
        try {
            if (!is_numeric($round_id)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $game_round = $this->game_room_service->fetchSpinWheelGameRound('id', $round_id);
            if (!$game_round) {
                return $this->errorResponse('Game Round not found', 404);
            }
            $result = $this->game_room_service->calculateResultForSpinWheel($game_round);
            DB::commit();
            return $this->successResponse($result, 200, 'Result of spin wheel.');
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function finishRoundForSpinWheel($round_id)
    {
        DB::beginTransaction();
        try {
            if (!is_numeric($round_id)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $game_round = $this->game_room_service->fetchSpinWheelGameRound('id', $round_id);
            if (!$game_round) {
                return $this->errorResponse('Game Round not found', 404);
            }
            $this->game_room_service->finishGameRoundForSpinWheel($game_round);
            DB::commit();
            return $this->successResponse([], 200, 'Finished the round successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function enterCoinFlipGameRoom($gameRoomId)
    {
        try {
            if (!is_numeric($gameRoomId)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $game_room = $this->game_room_service->whereFirst('id', $gameRoomId);
            if (!$game_room) {
                return $this->errorResponse('Game Room not found', 404);
            }
            $user = auth('api-user')->user();
            $user_balance = $user->balance;
            $min_bet = $game_room->gameRule->min_bet_amount;
            if ($user_balance < $min_bet) {
                return $this->errorResponse('Your balance is lower than the minimum bet for this game room.', 409);
            }

            $baseUrl = $baseUrl = config('services.web_socket.base_url');
            $response = Http::withHeaders([
                'X-Internal-Secret' => config('services.web_socket.internal_secret'),
            ])->get("{$baseUrl}/api/internal/rooms/{$gameRoomId}/coin-flip/user-count");

            if (!$response['success']) {
                logger()->error("Calling to websocket server fails::");
                return $this->errorResponse('Something went wrong!', 500);
            }
            if ($response['userCount'] == $game_room->gameRule->user_limit ?? 0) {
                return $this->errorResponse('Game room is full. Please join again later.', 409);
            }

            return $this->successResponse(['room_id' => $game_room->id], 200, 'Entered the room successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function startCoinFlipGameRound($gameRoomId)
    {
        try {
            if (!is_numeric($gameRoomId)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $game_room = $this->game_room_service->whereFirst('id', $gameRoomId);
            if (!$game_room) {
                return $this->errorResponse('Game Room not found', 404);
            }

            $running_round_exist = $this->game_room_service->checkIfActiveRoundExistOrNotForCoinFlip($gameRoomId);
            if ($running_round_exist['exist']) {
                return $this->errorResponse('Previous round is not finished yet.', 409);
            }
            $new_round = $this->game_room_service->createNewRoundForCoinFlip($gameRoomId);
            $new_round->load(['gameRoom.game', 'gameRoom.gameRule', 'bets.user']);
            return $this->successResponse($new_round, 200, 'Round created successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function placeBetForCoinFlip(CoinFlipPlaceBetRequest $request, $round_id)
    {
        DB::beginTransaction();
        try {
            if (!is_numeric($round_id)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $validator = Validator::make($request->all(), $request->rules(), $request->messages());
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
            $validated = $request->validated();
            $game_round = $this->game_room_service->fetchCoinFlipGameRound('id', $round_id);

            if (!$game_round) {
                return $this->errorResponse('Game Round not found', 404);
            }
            if ($game_round->status == "flipping") {
                return $this->errorResponse('Betting is locked for this round.', 409);
            }
            if ($game_round->status == "finished") {
                return $this->errorResponse('This round is already finished.', 409);
            }

            $user = auth('api-user')->user();
            $already_bet = $this->game_room_service->checkIfAlreadyBetorNotForCoinFlip($game_round->id, $user->id);
            $max_bet = $game_round->gameRoom->gameRule->max_bet_amount;
            $min_bet = $game_round->gameRoom->gameRule->min_bet_amount;
            if (!$already_bet) {
                if ($validated['bet_amount'] > $max_bet) {
                    return $this->errorResponse('Your bet amount is higher than the max bet amount.', 409);
                }
                if ($validated['bet_amount'] < $min_bet) {
                    return $this->errorResponse('Your bet amount is lower than the min bet amount.', 409);
                }
            } else {
                $bet_side = $this->game_room_service->getBetSide($game_round->id, $user->id);
                if ($bet_side !== $validated['bet_side']) {
                    return $this->errorResponse('You cannot bet again on another side.', 409);
                }
                $total_bet = $this->game_room_service->getTotalBetAmountOfTargetUserForCoinFlip($game_round->id, $user->id);
                $new_total_bet = $total_bet + $validated['bet_amount'];
                if ($new_total_bet > $max_bet) {
                    return $this->errorResponse('Your total bet amount is higher than the max bet amount.', 409);
                }
            }
            $gameRoomId = $game_round->gameRoom->id;
            $user_balance = $user->balance;

            if ($validated['bet_amount'] > $user_balance) {
                return $this->errorResponse('Insufficient balance.', 409);
            }

            $data = [
                'coin_flip_round_id' => $game_round->id,
                'user_id' => $user->id,
                'side' => $validated['bet_side'],
                'bet_amount' => $validated['bet_amount'],
            ];

            $user->withdraw($validated['bet_amount'], ['type' => 'coin_flip_bet', 'round_id' => $game_round->id]);
            $bet = $this->game_room_service->createBetForCoinFlip($data, $already_bet);
            $bet_info = $this->game_room_service->calculateBetInfoForCoinFlip($game_round->id);

            $baseUrl = $baseUrl = config('services.web_socket.base_url');
            $response = Http::withHeaders([
                'X-Internal-Secret' => config('services.web_socket.internal_secret'),
                'Accept' => 'application/json',
            ])->withBody(json_encode([
                            'bet_infos' => $bet_info
                        ]), 'application/json')->post("{$baseUrl}/api/internal/rooms/{$gameRoomId}/coin-flip/place-bet");

            if (!$response['success']) {
                logger()->error("Calling to websocket server fails::");
                return $this->errorResponse('Something went wrong!', 500);
            }
            DB::commit();
            return $this->successResponse([], 200, 'Placed bet successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function cancelBetForCoinFlip($roundId)
    {
        try {
            if (!is_numeric($roundId)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $game_round = $this->game_room_service->fetchCoinFlipGameRound('id', $roundId);
            if (!$game_round) {
                return $this->errorResponse('Game Round not found', 404);
            }
            if ($game_round->status !== 'betting') {
                return $this->errorResponse('Betting phase is done.Cannot cancel.', 409);
            }
            $hasBet = $game_round->bets()
                ->where('user_id', auth('api-user')->user()->id)
                ->exists();

            if (!$hasBet) {
                return $this->errorResponse('You have not placed a bet in this round.', 404);
            }
            $this->game_room_service->cancelBetForCoinFlip($game_round);
            $gameRoomId = $game_round->gameRoom->id;
            $bet_info = $this->game_room_service->calculateBetInfoForCoinFlip($game_round->id);

            $baseUrl = $baseUrl = config('services.web_socket.base_url');
            $response = Http::withHeaders([
                'X-Internal-Secret' => config('services.web_socket.internal_secret'),
                'Accept' => 'application/json',
            ])->withBody(json_encode([
                            'bet_infos' => $bet_info
                        ]), 'application/json')->post("{$baseUrl}/api/internal/rooms/{$gameRoomId}/coin-flip/cancel-bet");

            if (!$response['success']) {
                logger()->error("Calling to websocket server fails::");
                return $this->errorResponse('Something went wrong!', 500);
            }
            return $this->successResponse([], 200, 'Bet cancelled successfully.');
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
    public function requestResultForCoinFlip($round_id)
    {
        DB::beginTransaction();
        try {
            if (!is_numeric($round_id)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $game_round = $this->game_room_service->fetchCoinFlipGameRound('id', $round_id);
            if (!$game_round) {
                return $this->errorResponse('Game Round not found', 404);
            }
            $result = $this->game_room_service->calculateResultForCoinFlip($game_round);
            DB::commit();
            return $this->successResponse($result, 200, 'Result of coin flip.');
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }

    public function finishRoundForCoinFlip($round_id)
    {
        DB::beginTransaction();
        try {
            if (!is_numeric($round_id)) {
                return $this->errorResponse('ID must be an integer!', 422);
            }
            $game_round = $this->game_room_service->fetchCoinFlipGameRound('id', $round_id);
            if (!$game_round) {
                return $this->errorResponse('Game Round not found', 404);
            }
            $this->game_room_service->finishGameRoundForCoinFlip($game_round);
            DB::commit();
            return $this->successResponse([], 200, 'Finished the round successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e);
            return $this->errorResponse('Something went wrong!', 500);
        }
    }
}