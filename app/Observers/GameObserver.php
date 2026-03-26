<?php

namespace App\Observers;

use App\Models\Game;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;

class GameObserver
{
    protected function clearCache(): void
    {
        if (Cache::getStore() instanceof RedisStore) {
            Cache::tags(['games'])->flush();
            Cache::tags(['game_rooms'])->flush();
        }
    }

    public function created(Game $game): void
    {
        $this->clearCache();
    }

    public function updated(Game $game): void
    {
        $this->clearCache();
    }

    public function deleted(Game $game): void
    {
        $this->clearCache();
    }

    public function restored(Game $game): void
    {
        $this->clearCache();
    }

    public function forceDeleted(Game $game): void
    {
        //
    }
}
