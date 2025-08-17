<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProximityController;

Route::prefix('proximity')->group(function () {
    Route::post('/check', [ProximityController::class, 'checkProximity']);
    Route::post('/batch', [ProximityController::class, 'batchProximityCheck']);
});
