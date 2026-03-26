<?php

namespace App\Providers;

use App\Models\Game;
use App\Models\GameRoom;
use App\Models\GameRule;
use App\Models\PaymentMethod;
use App\Observers\GameObserver;
use App\Observers\GameRoomObserver;
use App\Observers\GameRuleObserver;
use App\Observers\PaymentMethodObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Game::observe(GameObserver::class);
        PaymentMethod::observe(PaymentMethodObserver::class);
        GameRule::observe(GameRuleObserver::class);
        GameRoom::observe(GameRoomObserver::class);
    }
}
