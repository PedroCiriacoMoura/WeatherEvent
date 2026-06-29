<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\EventWeatherController;
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

    Route::apiResource('events', EventController::class);

    Route::get('events/{event}/weather', [EventWeatherController::class, 'weather']);
    Route::get('events/{event}/health-score', [EventWeatherController::class, 'healthScore']);
    Route::get('events/{event}/recommendations', [EventWeatherController::class, 'recommendations']);
});
