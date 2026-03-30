<?php

namespace App\Http\Repositories\User;

use App\Http\Repositories\BaseRepo;
use App\Models\MahJongGameRule;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;


class MahJongGameRuleRepository extends BaseRepo
{
    public function __construct(MahJongGameRule $model)
    {
        parent::__construct($model);
    }

    public function getDataWithPaginationCached(
        int $perPage,
        int $page,
        $conditions
    ): array {
        $cacheKey = $this->buildCacheKey(
            'games:list',
            [
                'page' => $page,
                'per' => $perPage,
                'conditions' => $conditions,
            ]
        );

        $ttl = now()->addMinutes(10);

        $cache = Cache::getStore() instanceof RedisStore
            ? Cache::tags(['game_rules', 'game_rule_list'])
            : Cache::store();

        $cached = $cache->has($cacheKey);

        $results = $cache->remember($cacheKey, $ttl, function () use ($perPage, $page, $conditions) {
            return $this->getDataWithPagination(
                perPage: $perPage,
                page: $page,
                conditions: $conditions
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

    public function allRules()
    {
        $data = MahJongGameRule::with(['game', 'fees'])->get();
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function find($id)
    {
        $data = MahJongGameRule::with(['game', 'fees'])->find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }
}
