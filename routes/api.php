<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceTypeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'getDataMe'])->middleware('auth:sanctum');
});

Route::prefix('device-type')->group(function () {
    Route::get('/', [DeviceTypeController::class, 'index']);
    Route::get('/{id}', [DeviceTypeController::class, 'show']);
    Route::post('/', [DeviceTypeController::class, 'store']);
    Route::put('/{id}', [DeviceTypeController::class, 'update']);
    Route::delete('/{id}', [DeviceTypeController::class, 'destroy']);
});


