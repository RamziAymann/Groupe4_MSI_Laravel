<?php

use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\StatsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('clients', ClientController::class);
    Route::get('clients/search', [ClientController::class, 'search']);
});

Route::prefix('v1')->group(function () {
    // ... routes existantes ...
    
    // Routes de monitoring
    Route::get('stats', [StatsController::class, 'index']);
    Route::get('health', [StatsController::class, 'health']);
});