<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\CoinFlipBetController;
use App\Http\Controllers\Admin\DepositController;
use App\Http\Controllers\Admin\GameController;
use App\Http\Controllers\Admin\GameRoomController;
use App\Http\Controllers\Admin\GameRuleController;
use App\Http\Controllers\Admin\GlobalCommissionSettingController;
use App\Http\Controllers\Admin\MahJongGameRuleController;
use App\Http\Controllers\Admin\MoneyTransferController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SpinWheelBetController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WithdrawController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Middleware\CheckPermission;

Route::prefix('admins')->group(function () {
    Route::middleware('auth:api-admin')->group(function () {
        Route::post('/auth/logout', [AdminController::class, 'logout']);
        Route::get('permissions', [PermissionController::class, 'index']);

        Route::patch('{id}/toggle-status', [AdminController::class, 'toggleActive'])->middleware(CheckPermission::class . ':admin_update');
        Route::get('/all', [AdminController::class, 'index'])->middleware(CheckPermission::class . ':admin_view');
        Route::post('', [AdminController::class, 'create'])->middleware(CheckPermission::class . ':admin_create');
        Route::put('{id}', [AdminController::class, 'update'])->middleware(CheckPermission::class . ':admin_update');
        Route::delete('{id}', [AdminController::class, 'delete'])->middleware(CheckPermission::class . ':admin_delete');
        Route::get('{id}', [AdminController::class, 'findOrFail'])->middleware(CheckPermission::class . ':admin_view');

        Route::prefix('users')->group(function () {
            Route::patch('{id}/toggle-status', [UserController::class, 'toggleActive'])->middleware(CheckPermission::class . ':user_update');
            Route::get('/all', [UserController::class, 'index'])->middleware(CheckPermission::class . ':user_view');
            Route::post('', [UserController::class, 'create'])->middleware(CheckPermission::class . ':user_create');
            Route::put('{id}/verify', [UserController::class, 'verifyUser'])->middleware(CheckPermission::class . ':user_update');
            Route::put('{id}/reset-password', [UserController::class, 'resetUserPassword'])->middleware(CheckPermission::class . ':user_update');
            Route::get('{id}', [UserController::class, 'findOrFail'])->middleware(CheckPermission::class . ':user_view');
        });

        Route::prefix('masters')->group(function () {
            Route::patch('{id}/toggle-status', [MasterController::class, 'toggleActive'])->middleware(CheckPermission::class . ':master_update');
            Route::get('/all', [MasterController::class, 'index'])->middleware(CheckPermission::class . ':master_view');
            Route::post('', [MasterController::class, 'create'])->middleware(CheckPermission::class . ':master_create');
            Route::put('{id}', [MasterController::class, 'update'])->middleware(CheckPermission::class . ':master_update');
            Route::delete('{id}', [MasterController::class, 'delete'])->middleware(CheckPermission::class . ':master_delete');
            Route::get('{id}', [MasterController::class, 'findOrFail'])->middleware(CheckPermission::class . ':master_view');
        });

        Route::prefix('agents')->group(function () {
            Route::get('/all', [AgentController::class, 'index'])->middleware(CheckPermission::class . ':agent_view');
            Route::get('{id}', [AgentController::class, 'findOrFail'])->middleware(CheckPermission::class . ':agent_view');
        });

        Route::prefix('payment-methods')->group(function () {
            Route::patch('{id}/toggle-status', [PaymentMethodController::class, 'toggleActive'])->middleware(CheckPermission::class . ':payment_method_update');
            Route::get('/all', [PaymentMethodController::class, 'index'])->middleware(CheckPermission::class . ':payment_method_view');
            Route::post('', [PaymentMethodController::class, 'create'])->middleware(CheckPermission::class . ':payment_method_create');
            Route::put('{id}', [PaymentMethodController::class, 'update'])->middleware(CheckPermission::class . ':payment_method_update');
            Route::get('{id}', [PaymentMethodController::class, 'findOrFail'])->middleware(CheckPermission::class . ':payment_method_view');
        });

        // user deposit
        Route::prefix('user-deposit-requests')->group(function () {
            Route::get('/all', [DepositController::class, 'index'])->middleware(CheckPermission::class . ':user_deposit_request_view');
            Route::get('/manual', [DepositController::class, 'getManualDepositLists'])->middleware(CheckPermission::class . ':user_deposit_request_view');

            Route::put('{id}/approve', [DepositController::class, 'approveUserDepositRequest'])->middleware(CheckPermission::class . ':user_deposit_request_action');
            Route::put('{id}/reject', [DepositController::class, 'rejectUserDepositRequest'])->middleware(CheckPermission::class . ':user_deposit_request_action');
            Route::get('{id}', [DepositController::class, 'findOrFail'])->middleware(CheckPermission::class . ':user_deposit_request_view');
        });
        Route::post('user-deposits/manual-create', [DepositController::class, 'manualCreateUserDeposit'])->middleware(CheckPermission::class . ':manual_create_user_deposit');

        // user withdraw
        Route::prefix('user-withdraw-requests')->group(function () {
            Route::get('/all', [WithdrawController::class, 'index'])->middleware(CheckPermission::class . ':user_withdraw_request_view');
            Route::get('/manual', [WithdrawController::class, 'getManualWithdrawLists'])->middleware(CheckPermission::class . ':user_withdraw_request_view');

            Route::put('{id}/approve', [WithdrawController::class, 'approveUserWithdrawRequest'])->middleware(CheckPermission::class . ':user_withdraw_request_action');
            Route::put('{id}/reject', [WithdrawController::class, 'rejectUserWithdrawRequest'])->middleware(CheckPermission::class . ':user_withdraw_request_action');
            Route::get('{id}', [WithdrawController::class, 'findOrFail'])->middleware(CheckPermission::class . ':user_withdraw_request_view');
        });
        Route::post('user-withdraws/manual-create', [WithdrawController::class, 'manualCreateUserWithdraw'])->middleware(CheckPermission::class . ':manual_create_user_withdraw');

        // agent withdraw
        Route::prefix('agent-withdraw-requests')->group(function () {
            Route::get('/all', [WithdrawController::class, 'agentWithdrawRequestList'])->middleware(CheckPermission::class . ':agent_withdraw_request_view');

            Route::put('{id}/approve', [WithdrawController::class, 'approveAgentWithdrawRequest'])->middleware(CheckPermission::class . ':agent_withdraw_request_action');
            Route::put('{id}/reject', [WithdrawController::class, 'rejectAgentWithdrawRequest'])->middleware(CheckPermission::class . ':agent_withdraw_request_action');
            // Route::get('{id}', [WithdrawController::class, 'findOrFail'])->middleware(CheckPermission::class . ':user_withdraw_request_view');
        });
        // Route::post('user-withdraws/manual-create', [WithdrawController::class, 'manualCreateUserWithdraw'])->middleware(CheckPermission::class . ':manual_create_user_withdraw');

        // master withdraw
        Route::prefix('master-withdraw-requests')->group(function () {
            Route::get('/all', [WithdrawController::class, 'masterWithdrawRequestList'])->middleware(CheckPermission::class . ':master_withdraw_request_view');

            Route::put('{id}/approve', [WithdrawController::class, 'approveMasterWithdrawRequest'])->middleware(CheckPermission::class . ':master_withdraw_request_action');
            Route::put('{id}/reject', [WithdrawController::class, 'rejectMasterWithdrawRequest'])->middleware(CheckPermission::class . ':master_withdraw_request_action');
            // Route::get('{id}', [WithdrawController::class, 'findOrFail'])->middleware(CheckPermission::class . ':user_withdraw_request_view');
        });

        Route::prefix('user-money-transfer-records')->group(function () {
            Route::get('/all', [MoneyTransferController::class, 'index'])->middleware(CheckPermission::class . ':money_transfer_record_view');
            Route::get('{id}', [MoneyTransferController::class, 'findOrFail'])->middleware(CheckPermission::class . ':money_transfer_record_view');
        });

        Route::prefix('notifications')->group(function () {
            Route::get('/deposit/all', [NotificationController::class, 'getDepositNotifications']);
            Route::get('/withdraw/all', [NotificationController::class, 'getWithdrawNotifications']);
            Route::get('{id}', [NotificationController::class, 'findOrFail']);
            Route::post('/deposit/read', [NotificationController::class, 'readDepositNotifications']);
            Route::post('/withdraw/read', [NotificationController::class, 'readWithdrawNotifications']);
        });

        Route::prefix('global-commission-settings')->group(function () {
            Route::get('/all', [GlobalCommissionSettingController::class, 'index'])->middleware(CheckPermission::class . ':global_commission_setting_view');
            Route::put('{id}', [GlobalCommissionSettingController::class, 'update'])->middleware(CheckPermission::class . ':global_commission_setting_update');
        });

        Route::prefix('games')->group(function () {
            Route::patch('{id}/toggle-status', [GameController::class, 'toggleActive'])->middleware(CheckPermission::class . ':game_update');
            Route::get('/all', [GameController::class, 'index'])->middleware(CheckPermission::class . ':game_view');
            Route::get('{id}', [GameController::class, 'findOrFail'])->middleware(CheckPermission::class . ':game_view');
        });

        Route::prefix('mah-jong-game-rules')->group(function () {
            Route::patch('{id}/toggle-status', [MahJongGameRuleController::class, 'toggleActive'])->middleware(CheckPermission::class . ':game_rule_update');
            // Route::get('{game_type_id}/filter-by-game', [MahJongGameRuleController::class, 'getRulesByGameType'])->middleware(CheckPermission::class . ':game_rule_view');
            Route::get('all', [MahJongGameRuleController::class, 'index'])->middleware(CheckPermission::class . ':game_rule_view');
            Route::post('', [MahJongGameRuleController::class, 'create'])->middleware(CheckPermission::class . ':game_rule_create');
            Route::put('{id}', [MahJongGameRuleController::class, 'update'])->middleware(CheckPermission::class . ':game_rule_update');
            Route::get('{id}', [MahJongGameRuleController::class, 'findOrFail'])->middleware(CheckPermission::class . ':game_rule_view');
        });

        Route::prefix('game-rooms')->group(function () {
            Route::patch('{id}/toggle-status', [GameRoomController::class, 'toggleActive'])->middleware(CheckPermission::class . ':game_room_update');
            Route::get('all', [GameRoomController::class, 'index'])->middleware(CheckPermission::class . ':game_room_view');
            Route::post('', [GameRoomController::class, 'create'])->middleware(CheckPermission::class . ':game_room_create');
            Route::put('{id}', [GameRoomController::class, 'update'])->middleware(CheckPermission::class . ':game_room_update');
            Route::get('{id}', [GameRoomController::class, 'findOrFail'])->middleware(CheckPermission::class . ':game_room_view');
        });

        Route::prefix('spin-wheel')->group(function () {
            Route::get('bet-histories', [SpinWheelBetController::class, 'index'])->middleware(CheckPermission::class . ':game_view');
        });

        Route::prefix('coin-flip')->group(function () {
            Route::get('bet-histories', [CoinFlipBetController::class, 'index'])->middleware(CheckPermission::class . ':game_view');
        });

        // reports
        Route::prefix('reports')->group(function () {
            Route::get('/house-cut', [ReportController::class, 'getHouseCutReports'])->middleware(CheckPermission::class . ':report_view');
            Route::get('/house-cut/daily-lists', [ReportController::class, 'getDailyHouseCutReportsWithPagination'])->middleware(CheckPermission::class . ':report_view');

            Route::get('/deposit', [ReportController::class, 'getDepositReports'])->middleware(CheckPermission::class . ':report_view');
            Route::get('/deposit/daily-lists', [ReportController::class, 'getDailyDepositReportsWithPagination'])->middleware(CheckPermission::class . ':report_view');

            Route::get('/money-transfer', [ReportController::class, 'getMoneyTransferReports'])->middleware(CheckPermission::class . ':report_view');
            Route::get('/money-transfer/daily-lists', [ReportController::class, 'getDailyMoneyTransferReportsWithPagination'])->middleware(CheckPermission::class . ':report_view');

            Route::get('/profit', [ReportController::class, 'getProfitReports'])->middleware(CheckPermission::class . ':report_view');
            Route::get('/profit/daily-lists', [ReportController::class, 'getDailyProfitReportsWithPagination'])->middleware(CheckPermission::class . ':report_view');

            Route::get('/user-game-history/lists', [ReportController::class, 'getUserGameHistory'])->middleware(CheckPermission::class . ':report_view');
            
            Route::get('/monthly-master-commission', [ReportController::class, 'getMasterCommissionByMonth'])->middleware(CheckPermission::class . ':report_view');
            Route::get('/monthly-agent-commission', [ReportController::class, 'getAgentCommissionByMonth'])->middleware(CheckPermission::class . ':report_view');
        });
    });
    Route::post('/auth/login', [AdminController::class, 'login']);
});
