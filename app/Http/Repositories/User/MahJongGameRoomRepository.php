<?php

namespace App\Http\Repositories\User;

use App\Http\Repositories\BaseRepo;
use App\Models\MahJongGameRoom;

use App\Models\MahJongGameRound;
use App\Models\MahJongMatch;
use App\Models\MahJongRoomPlayer;
use App\Models\MahJongRoundPlayer;
use DB;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;

class MahJongGameRoomRepository extends BaseRepo
{
    public function __construct(MahJongGameRoom $model)
    {
        parent::__construct($model);
    }

    public function getDataWithPaginationCached(
        int $perPage,
        int $page,
        ?array $searches = null,
        ?array $conditions = null,
        ?string $status = null,
        array $with = []
    ): array {
        $cacheKey = $this->buildCacheKey(
            'game_rooms:list',
            [
                'page' => $page,
                'per' => $perPage,
                'status' => $status,
                'searches' => $searches,
                'conditions' => $conditions,
                'with' => $with,
            ]
        );

        $ttl = now()->addMinutes(5);

        $tags = [
            'game_rooms',
            isset($conditions['game_id']) ? "game:{$conditions['game_id']}" : null,
        ];

        $tags = array_filter($tags);

        $cache = Cache::getStore() instanceof RedisStore
            ? Cache::tags($tags)
            : Cache::store();

        $cached = $cache->has($cacheKey);

        $results = $cache->remember($cacheKey, $ttl, function () use ($perPage, $page, $searches, $conditions, $status, $with) {
            return $this->getDataWithPagination(
                perPage: $perPage,
                page: $page,
                searches: $searches,
                conditions: $conditions,
                status: $status,
                with: $with
            );
        });

        $results['meta']['cached'] = $cached;

        return $results;
    }

    protected function buildCacheKey(string $prefix, array $params): string
    {
        return $prefix . ':' . collect($params)
            ->filter(fn($v) => $v !== null && $v !== [] && $v !== '')
            ->map(function ($value) {
                if (is_array($value)) {
                    return collect($value)->sortKeys()->toJson();
                }
                return $value;
            })
            ->sortKeys()
            ->map(fn($v, $k) => "{$k}={$v}")
            ->implode(':');
    }


    public function find($id)
    {
        $data = MahJongGameRoom::with(['game', 'gameRule.fees'])->find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function allRooms()
    {
        $data = MahJongGameRoom::with(['game', 'gameRule.fees'])->get();
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function getCurrentMatch($roomId)
    {
        $match = MahJongMatch::where('mah_jong_game_room_id', $roomId)
            ->where('status', '!=', 'finished')
            ->latest()
            ->first();
        return $match;
    }

    public function createNewMatch($roomId, $rule)
    {
        $match = MahJongMatch::create([
            'mah_jong_game_room_id' => $roomId,
            'total_rounds' => $rule->round_qty_per_match,
        ]);
        return $match;
    }

    public function findCurrentRound($roomId)
    {
        $match = MahJongMatch::where('mah_jong_game_room_id', $roomId)
            ->where('status', '!=', 'finished')
            ->latest()
            ->first();
        $round = MahJongGameRound::where('mah_jong_match_id', $match->id)
            ->where('status', '!=', 'finished')
            ->latest()
            ->first();
        return $round;
    }

    public function findRound($roundId)
    {

        $round = MahJongGameRound::find($roundId);
        return $round;
    }

    public function createNewRound($roomId)
    {
        $match = MahJongMatch::where('mah_jong_game_room_id', $roomId)
            ->where('status', '!=', 'finished')
            ->latest()
            ->first();
        $previous_round = MahJongGameRound::where('mah_jong_match_id', $match->id)
            ->where('status', 'finished')
            ->latest()
            ->first();
        $round_no = $previous_round
            ? $previous_round->round_no + 1
            : 1;
        $round = MahJongGameRound::create([
            'mah_jong_match_id' => $match->id,
            'round_no' => $round_no,
            'status' => 'playing',
        ]);

        return $round;
    }

    public function get_player_count($roomId)
    {
        return MahJongRoomPlayer::where('mah_jong_game_room_id', $roomId)
            ->where('is_active', true)
            ->count();
    }

    public function addUserIntoRoomPlayers($roomId, $userId)
    {
        $player = MahJongRoomPlayer::where('mah_jong_game_room_id', $roomId)
            ->where('user_id', $userId)
            ->first();

        if ($player) {
            $player->is_active = true;
            $player->save();
        } else {
            MahJongRoomPlayer::create([
                'mah_jong_game_room_id' => $roomId,
                'user_id' => $userId,
                'is_active' => true,
            ]);
        }
    }

    public function assign_seat_positions($room, $round)
    {
        $players = MahJongRoomPlayer::where('mah_jong_game_room_id', $room->id)
            ->where('is_active', true)
            ->with('user')
            ->orderBy('id', 'asc')
            ->get();

        $seat_position = 1;
        $roundPlayers = [];

        foreach ($players as $each) {
            MahJongRoundPlayer::create([
                'mah_jong_game_round_id' => $round->id,
                'user_id' => $each->user_id,
                'seat_position' => $seat_position,
            ]);

            $roundPlayers[] = [
                'user_id' => $each->user_id,
                'name' => $each->user->name ?? null,
                'seat_position' => $seat_position,
            ];

            $seat_position++;
        }

        return $roundPlayers;
    }

    public function updateStatusOfLeaveUser($roomId, $userId)
    {
        MahJongRoomPlayer::where('mah_jong_game_room_id', $roomId)
            ->where('user_id', $userId)
            ->update([
                'is_active' => false,
            ]);

        $matchId = MahJongMatch::where('mah_jong_game_room_id', $roomId)
            ->where('status', '!=', 'finished')
            ->value('id');

        if (!$matchId) {
            return;
        }

        $roundId = MahJongGameRound::where('mah_jong_match_id', $matchId)
            ->where('status', '!=', 'finished')
            ->value('id');

        if (!$roundId) {
            return;
        }

        MahJongRoundPlayer::where('mah_jong_game_round_id', $roundId)
            ->where('user_id', $userId)
            ->update([
                'is_active' => false,
                'is_auto' => true,
            ]);
    }

    public function updateActiveStatusOfRejoinUser($roundId, $userId)
    {
        MahJongRoundPlayer::where('mah_jong_game_round_id', $roundId)
            ->where('user_id', $userId)
            ->update([
                'is_active' => true,
                'is_auto' => false
            ]);
    }

    public function endRound($round)
    {
        $round->status = 'finished';
        $round->save();
    }

    public function getShuffledTiles()
    {
        $tiles = DB::table('mah_jong_tiles')
            ->select(
                'id',
                'type',
                'number',
                'copy_no'
            )
            ->get()
            ->toArray();

        $tiles = array_map(function ($tile) {
            return [
                'id' => $tile->id,
                'type' => $tile->type,
                'number' => $tile->number,
                'copy_no' => $tile->copy_no,
            ];
        }, $tiles);

        shuffle($tiles);

        return $tiles;
    }
}
