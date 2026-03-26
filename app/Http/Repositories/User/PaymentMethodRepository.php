<?php

namespace App\Http\Repositories\User;

use App\Http\Repositories\BaseRepo;
use App\Models\PaymentMethod;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;


class PaymentMethodRepository extends BaseRepo
{
    public function __construct(PaymentMethod $model)
    {
        parent::__construct($model);
    }

    public function find($id)
    {
        $data = PaymentMethod::find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function getDataWithPaginationCached(
        int $perPage,
        int $page,
        ?array $searches = null,
        ?string $status = null
    ): array {
        $cacheKey = $this->buildCacheKey(
            'payment_methods:list',
            [
                'page' => $page,
                'per' => $perPage,
                'status' => $status,
                'search' => $searches,
            ]
        );

        $ttl = now()->addMinutes(10);

        $cache = Cache::getStore() instanceof RedisStore
            ? Cache::tags(['payment_methods', 'payment_method_list'])
            : Cache::store();

        $cached = $cache->has($cacheKey);

        $results = $cache->remember($cacheKey, $ttl, function () use ($perPage, $page, $searches, $status) {
            return $this->getDataWithPagination(
                perPage: $perPage,
                page: $page,
                searches: $searches,
                status: $status
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
}
