<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProgramController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('jwt')->group(function () {
        Route::get('/me', [AccountController::class, 'me']);
        Route::put('/me/password', [AccountController::class, 'updatePassword']);
        Route::get('/programs', [ProgramController::class, 'index']);
        Route::post('/application', [ApplicationController::class, 'store']);
        Route::get('/application', [ApplicationController::class, 'show']);
        Route::put('/application', [ApplicationController::class, 'update']);

        // ponytail: fixture route proving role:admin gating works; no admin API is
        // planned (admin surface is web-only) — remove if that stays true forever.
        Route::middleware('role:admin')->group(function () {
            Route::get('/admin-probe', fn () => response()->json(['ok' => true]));
        });
    });
});
