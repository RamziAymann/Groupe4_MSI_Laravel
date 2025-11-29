<?php

use App\Http\Controllers\API\ClientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('clients', ClientController::class);
    Route::get('clients/search', [ClientController::class, 'search']);
});