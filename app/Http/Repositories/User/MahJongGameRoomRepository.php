<?php

namespace App\Http\Repositories\User;

use App\Http\Repositories\BaseRepo;
use App\Models\MahJongGameRoom;

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
}
