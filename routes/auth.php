<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->controller(AuthController::class)
    ->group(function () {
        Route::post('login', 'login');
        Route::get('refresh', 'refresh');
        Route::get('me', 'me')->middleware('auth:api');
        Route::post('logout', 'logout')->middleware('auth:api');
    });
