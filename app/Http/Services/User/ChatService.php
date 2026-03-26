<?php

namespace App\Http\Services\User;

use App\Http\Repositories\User\ChatRepository;
use DB;
use Exception;
use Illuminate\Support\Facades\Http;
use function PHPUnit\Framework\throwException;

class ChatService
{
    protected $chat_repository;

    public function __construct(ChatRepository $chat_repository)
    {
        $this->chat_repository = $chat_repository;
    }

    public function getDataWithPagination(
        int $perPage = 10,
        int $page = 1,
        string $orderBy = 'created_at',
        array $searches = null,
        array $conditions = [],
        array $orConditions = [],
        array $with = [],
        ?array $whereHas = null,
        ?string $status = null
    ) {
        try {
            $result = $this->chat_repository->getDataWithPagination(page: $page, perPage: $perPage, conditions: $conditions, with: $with);
            $result['data'] = $result['data']->reverse()->values();
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch chat data with pagination: ' . $e->getMessage());
            throw $e;
        }
    }

    public function get_game_type(int $game_room_id)
    {
        try {
            $result = $this->chat_repository->get_game_type($game_room_id);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch game type: ' . $e->getMessage());
            throw $e;
        }
    }

    public function sendMessage($data, $game_type)
    {
        DB::beginTransaction();
        try {
            $result = $this->chat_repository->saveMessage($data);
            $result->load(['user', 'gameRoom']);
            $baseUrl = $baseUrl = config('services.web_socket.base_url');
            $gameRoomId = $data['game_room_id'];
            $response = Http::withHeaders([
                'X-Internal-Secret' => config('services.web_socket.internal_secret'),
            ])->post("{$baseUrl}/api/internal/rooms/{$gameRoomId}/send-message", [
                        'game_type' => $game_type,
                        'data' => $result,
                    ]);

            if ($response['success'] !== 'true') {
                logger()->error("Calling to websocket server to send message fails::");
                DB::rollBack();
                return false;
            }

            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error('Error : Failed to send chat message: ' . $e->getMessage());
            throw $e;
        }
    }
}
