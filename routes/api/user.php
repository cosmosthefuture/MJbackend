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

        Route::post('/transfer-money', [MoneyTransferController::class, 'create']);
        Route::post('/money-transfer/confirm', [MoneyTransferController::class, 'accept']);
        Route::post('/money-transfer/cancel', [MoneyTransferController::class, 'reject']);

        Route::prefix('payment-methods')->group(function () {
            Route::get('/all', [PaymentMethodController::class, 'index']);
            Route::get('{id}', [PaymentMethodController::class, 'findOrFail']);
        });

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
            Route::get('{id}', [MahJongGameRoomController::class, 'findOrFail']);
        });
        Route::post('game-rounds/{id}/spin-wheel/place-bet', [GameRoomController::class, 'placeBetForSpinWheel']);
        Route::post('game-rounds/{id}/spin-wheel/cancel-bet', [GameRoomController::class, 'cancelBetForSpinWheel']);
        Route::post('game-rounds/{id}/coin-flip/place-bet', [GameRoomController::class, 'placeBetForCoinFlip']);
        Route::post('game-rounds/{id}/coin-flip/cancel-bet', [GameRoomController::class, 'cancelBetForCoinFlip']);

        // chat message
        Route::prefix('chat-messages')->group(function () {
            Route::get('/all', [ChatController::class, 'index']);
            Route::post('/send-message', [ChatController::class, 'sendMessage']);
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
    // spin wheel
    Route::post('game-rooms/{id}/spin-wheel/start-round', [GameRoomController::class, 'startSpinWheelGameRound'])->middleware(CheckInternalSecret::class);
    Route::post('game-rounds/{id}/spin-wheel/request-result', [GameRoomController::class, 'requestResultForSpinWheel'])->middleware(CheckInternalSecret::class);
    Route::post('game-rounds/{id}/spin-wheel/finish', [GameRoomController::class, 'finishRoundForSpinWheel'])->middleware(CheckInternalSecret::class);

    // coin flip
    Route::post('game-rooms/{id}/coin-flip/start-round', [GameRoomController::class, 'startCoinFlipGameRound'])->middleware(CheckInternalSecret::class);
    Route::post('game-rounds/{id}/coin-flip/request-result', [GameRoomController::class, 'requestResultForCoinFlip'])->middleware(CheckInternalSecret::class);
    Route::post('game-rounds/{id}/coin-flip/finish', [GameRoomController::class, 'finishRoundForCoinFlip'])->middleware(CheckInternalSecret::class);

    Route::post('game-rooms', [GameRoomController::class, 'getAllRooms'])->middleware(CheckInternalSecret::class);
    Route::post('game-rooms/{id}', [GameRoomController::class, 'findOrFail'])->middleware(CheckInternalSecret::class);
});