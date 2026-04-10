<?php

use App\Http\Controllers\Agent\AgentController;
use App\Http\Controllers\Agent\NotificationController;
use App\Http\Controllers\Agent\PaymentMethodController;
use App\Http\Controllers\Agent\UserController;
use App\Http\Controllers\Agent\WithdrawController;

Route::prefix('agents')->group(function () {
    Route::middleware('auth:api-agent')->group(function () {
        Route::post('/auth/logout', [AgentController::class, 'logout']);

        Route::prefix('users')->group(function () {
            Route::post('/add-money', [UserController::class, 'addMoneyToUser']);
            Route::post('/withdraw-money', [UserController::class, 'withdrawMoneyFromUser']);

            Route::get('/deposit-lists', [UserController::class, 'UserDepositLists']);
            Route::get('/withdraw-lists', [UserController::class, 'UserWithdrawLists']);

            Route::patch('{id}/toggle-status', [UserController::class, 'toggleActive']);
            Route::get('/all', [UserController::class, 'index']);
            Route::post('', [UserController::class, 'create']);
            Route::put('{id}/verify', [UserController::class, 'verifyUser']);
            Route::put('{id}/reset-password', [UserController::class, 'resetUserPassword']);
            Route::put('{id}', [UserController::class, 'update']);
            Route::get('{id}', [UserController::class, 'findOrFail']);
        });

        Route::prefix('payment-methods')->group(function () {
            Route::get('/all', [PaymentMethodController::class, 'index']);
            Route::get('{id}', [PaymentMethodController::class, 'findOrFail']);
        });

        Route::get('wallet-balance', [AgentController::class, 'getWalletBalance']);
        Route::get('/withdraw-history', [WithdrawController::class, 'agentWithdrawRequestList']);
        Route::post('/withdraw-requests/create', [WithdrawController::class, 'create']);

        Route::get('incentive-transactions', [AgentController::class, 'getAgentIncentiveTransactions']);
        Route::get('daily-wallet-summary', [AgentController::class, 'getAgentDailyWalletSummary']);
        Route::get('monthly-incentive-summary', [AgentController::class, 'getAgentMonthlyIncentiveSummary']);
        Route::get('wallet-records', [AgentController::class, 'getAgentWalletRecords']);

        Route::prefix('notifications')->group(function () {
            Route::get('/all', [NotificationController::class, 'index']);
            Route::get('{id}', [NotificationController::class, 'findOrFail']);
            Route::post('/read', [NotificationController::class, 'readAllNotifications']);
        });
    });

    Route::post('/auth/login', [AgentController::class, 'login']);
});