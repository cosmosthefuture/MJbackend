<?php

use App\Http\Controllers\User\ChatController;
use App\Http\Controllers\User\DepositController;
use App\Http\Controllers\User\GameController;
use App\Http\Controllers\User\GameRoomController;
use App\Http\Controllers\User\GameRuleController;
use App\Http\Controllers\User\MahJongGameRoomController;
use App\Http\Controllers\User\MahJongGameRuleController;
use App\Http\Controllers\User\MoneyTransferController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\User\PaymentMethodController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\WebSocketController;
use App\Http\Controllers\User\WithdrawController;
use App\Http\Middleware\CheckInternalSecret;

Route::prefix('users')->group(function () {
    Route::middleware('auth:api-user')->group(function () {
        Route::post('/auth/logout', [UserController::class, 'logout']);
        Route::post('/auth/change-password', [UserController::class, 'changePassword']);
        Route::get('find-by-phone', [MoneyTransferController::class, 'findUserByPhoneNumber']);

        Route::get('/deposit-requests/all', [DepositController::class, 'index']);
        Route::get('/deposit-requests/manual', [DepositController::class, 'getManualDeposits']);
        Route::post('/deposit-requests/create', [DepositController::class, 'create']);

        Route::get('/withdraw-requests/all', [WithdrawController::class, 'index']);
        Route::get('/withdraw-requests/manual', [WithdrawController::class, 'getManualWithdraws']);
        Route::post('/withdraw-requests/create', [WithdrawController::class, 'create']);

        Route::prefix('notifications')->group(function () {
            Route::get('/all', [NotificationController::class, 'index']);
            Route::get('{id}', [NotificationController::class, 'findOrFail']);
            Route::post('/read', [NotificationController::class, 'readAllNotifications']);
        });

        // wallet balance
        Route::get('wallet-balance', [UserController::class, 'getWalletBalance']);

        Route::prefix('mah-jong-game-rules')->group(function () {
            Route::get('/all', [MahJongGameRuleController::class, 'getAllRules']);
            Route::get('{id}', [MahJongGameRuleController::class, 'findOrFail']);
        });

        Route::prefix('mah-jong-game-rooms')->group(function () {
            Route::get('all', [MahJongGameRoomController::class, 'index']);
            Route::post('{id}/join', [MahJongGameRoomController::class, 'joinRoom']);

            Route::post('{id}/join-token', [MahJongGameRoomController::class, 'getJoinToken']);
            Route::get('{id}', [MahJongGameRoomController::class, 'findOrFail']);
        });

        Route::prefix('web-socket')->group(function () {
            Route::post('/token/generate', [WebSocketController::class, 'generateJwtTokenForWS']);
        });
    });

    Route::prefix('games')->group(function () {
        Route::get('/all', [GameController::class, 'index']);
        Route::get('{id}', [GameController::class, 'findOrFail']);
    });

    Route::post('/auth/login', [UserController::class, 'login']);
    Route::post('/auth/register', [UserController::class, 'register']);
    Route::post('/auth/reset-password', [UserController::class, 'resetPassword']);
    Route::post('/auth/verify-phone', [UserController::class, 'verifyPhoneNumber']);
    Route::post('/auth/check-otp', [UserController::class, 'check_otp']);
    Route::post('/auth/request-otp', [UserController::class, 'request_new_otp'])->middleware('throttle:1,1');
});

Route::prefix('internal')->group(function () {
    // mahjong
    Route::post('mah-jong-game-rooms/{id}/start-round', [MahJongGameRoomController::class, 'startRound'])->middleware(CheckInternalSecret::class);
    Route::post('mah-jong-game-rooms/{id}/leave-room', [MahJongGameRoomController::class, 'leaveRoom'])->middleware(CheckInternalSecret::class);

    Route::get('mah-jong-game-rooms/{id}/get-data', [MahJongGameRoomController::class, 'getRoomData'])->middleware(CheckInternalSecret::class);
    Route::get('mah-jong-game-rooms/{id}/get-current-match', [MahJongGameRoomController::class, 'getCurrentMatch'])->middleware(CheckInternalSecret::class);

    Route::post('mah-jong-game-rounds/{id}/update-round-player-active-status', [MahJongGameRoomController::class, 'updateRoundPlayerActiveStatus'])->middleware(CheckInternalSecret::class);
    Route::get('mah-jong-game-rounds/get-shuffled-tiles', [MahJongGameRoomController::class, 'getShuffledTiles'])->middleware(CheckInternalSecret::class);

    Route::post('mah-jong-game-rounds/{id}/end-round', [MahJongGameRoomController::class, 'endRound'])->middleware(CheckInternalSecret::class);

});

// temporary , delete later
Route::post('/dev/reset', function (Request $request) {

    // fresh migrate + seed
    Artisan::call('migrate:fresh', [
        '--seed' => true,
    ]);

    // clear cache, config, routes, views
    Artisan::call('optimize:clear');
    return response()->json([
        'message' => 'System reset completed',
        'optimize_clear_output' => Artisan::output(),
    ]);
});