<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HealthController::class, 'ping']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1'); // anti brute-force

Route::post('/refresh', [AuthController::class, 'refresh'])
    ->middleware(['auth:sanctum', 'ability:refresh-api']);

Route::middleware(['auth:sanctum', 'ability:access-api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});
