<?php

namespace App\Observers;

use App\Models\GameRoom;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;

class GameRoomObserver
{
    protected function clearCache(): void
    {
        if (Cache::getStore() instanceof RedisStore) {
            Cache::tags(['game_rooms'])->flush();
        }
    }

    public function created(GameRoom $game_room): void
    {
        $this->clearCache();
    }

    public function updated(GameRoom $game_room): void
    {
        $this->clearCache();
    }

    public function deleted(GameRoom $game_room): void
    {
        $this->clearCache();
    }

    public function restored(GameRoom $game_room): void
    {
        $this->clearCache();
    }

    public function forceDeleted(GameRoom $game_room): void
    {
        //
    }
}
