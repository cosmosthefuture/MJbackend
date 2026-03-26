<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Middleware\CheckPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function () {
    return "It works";
});

require __DIR__ . '/api/admin.php';
require __DIR__ . '/api/user.php';
require __DIR__ . '/api/master.php';
require __DIR__ . '/api/agent.php';