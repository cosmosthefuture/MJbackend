<?php

use App\Http\Controllers\Master\AgentController;
use App\Http\Controllers\Master\MasterController;
use App\Http\Controllers\Master\NotificationController;
use App\Http\Controllers\Master\PaymentMethodController;
use App\Http\Controllers\Master\WithdrawController;

Route::prefix('masters')->group(function () {
    Route::middleware('auth:api-master')->group(function () {
        Route::post('/auth/logout', [MasterController::class, 'logout']);

        Route::get('wallet-balance', [MasterController::class, 'getWalletBalance']);
        Route::post('/withdraw-requests/create', [WithdrawController::class, 'create']);
        Route::get('/withdraw-history', [WithdrawController::class, 'masterWithdrawRequestList']);

        Route::get('daily-wallet-summary', [MasterController::class, 'getMasterDailyWalletSummary']);
        Route::get('monthly-incentive-report', [MasterController::class, 'getMasterMonthlyIncentiveReport']);

        Route::prefix('agents')->group(function () {
            Route::patch('{id}/toggle-status', [AgentController::class, 'toggleActive']);
            Route::get('/all', [AgentController::class, 'index']);
            Route::post('', [AgentController::class, 'create']);
            Route::put('{id}', [AgentController::class, 'update']);
            Route::delete('{id}', [AgentController::class, 'delete']);
            Route::get('{id}', [AgentController::class, 'findOrFail']);
        });

        Route::prefix('notifications')->group(function () {
            Route::get('/all', [NotificationController::class, 'index']);
            Route::get('{id}', [NotificationController::class, 'findOrFail']);
            Route::post('/read', [NotificationController::class, 'readAllNotifications']);
        });

        Route::prefix('payment-methods')->group(function () {
            Route::get('/all', [PaymentMethodController::class, 'index']);
            Route::get('{id}', [PaymentMethodController::class, 'findOrFail']);
        });
    });

    Route::post('/auth/login', [MasterController::class, 'login']);
});