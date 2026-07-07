<?php

use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\AdminApplicationController;
use App\Http\Controllers\Web\ApplicationController;
use App\Http\Controllers\Web\ProgramController;
use App\Http\Controllers\Web\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (!auth()->check()) return redirect('/login');
    return auth()->user()->role === 'admin'
        ? redirect('/admin/applications')
        : redirect('/application');
});

Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);
Route::get('/register', [WebAuthController::class, 'showRegister']);
Route::post('/register', [WebAuthController::class, 'register']);
Route::post('/logout', [WebAuthController::class, 'logout']);

Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'show']);
    Route::post('/account/password', [AccountController::class, 'updatePassword']);
});

Route::middleware(['auth', 'role:candidate'])->group(function () {
    Route::get('/application/create', [ApplicationController::class, 'create']);
    Route::post('/application', [ApplicationController::class, 'store']);
    Route::get('/application', [ApplicationController::class, 'show']);
    Route::get('/application/edit', [ApplicationController::class, 'edit']);
    Route::put('/application', [ApplicationController::class, 'update']);
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/programs', [ProgramController::class, 'index']);
    Route::get('/programs/create', [ProgramController::class, 'create']);
    Route::post('/programs', [ProgramController::class, 'store']);
    Route::get('/programs/{program}/edit', [ProgramController::class, 'edit']);
    Route::put('/programs/{program}', [ProgramController::class, 'update']);
    Route::delete('/programs/{program}', [ProgramController::class, 'destroy']);

    Route::get('/admin/applications', [AdminApplicationController::class, 'index']);
    Route::get('/admin/applications/{application}', [AdminApplicationController::class, 'show']);
    Route::get('/admin/applications/{application}/edit', [AdminApplicationController::class, 'edit']);
    Route::put('/admin/applications/{application}', [AdminApplicationController::class, 'update']);
    Route::post('/admin/applications/{application}/verdict', [AdminApplicationController::class, 'verdict']);
    Route::delete('/admin/applications/{application}', [AdminApplicationController::class, 'destroy']);
});
