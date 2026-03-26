<?php

namespace App\Observers;

use App\Models\GameRule;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;

class GameRuleObserver
{
    protected function clearCache(): void
    {
        if (Cache::getStore() instanceof RedisStore) {
            Cache::tags(['game_rooms'])->flush();
        }
        if (Cache::getStore() instanceof RedisStore) {
            Cache::tags(['game_rules'])->flush();
        }
    }

    public function created(GameRule $game_rule): void
    {
        $this->clearCache();
    }

    public function updated(GameRule $game_rule): void
    {
        $this->clearCache();
    }

    public function deleted(GameRule $game_rule): void
    {
        $this->clearCache();
    }

    public function restored(GameRule $game_rule): void
    {
        $this->clearCache();
    }

    public function forceDeleted(GameRule $game_rule): void
    {
        //
    }
}
