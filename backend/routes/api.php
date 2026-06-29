<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\EventWeatherController;
use App\Http\Controllers\HealthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [HealthController::class, 'ping']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('events', EventController::class);

    Route::get('events/{event}/weather', [EventWeatherController::class, 'weather']);
    Route::get('events/{event}/health-score', [EventWeatherController::class, 'healthScore']);
    Route::get('events/{event}/recommendations', [EventWeatherController::class, 'recommendations']);
});
