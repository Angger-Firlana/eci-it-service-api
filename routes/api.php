<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceTypeController;
use App\Http\Controllers\DeviceModelController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ServiceRequestController;

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

Route::prefix('device-model')->group(function(){
    Route::get('/', [DeviceModelController::class, 'index']);
    Route::get('/{id}', [DeviceModelController::class, 'show']);
    Route::post('/', [DeviceModelController::class, 'store']);
    Route::put('/{id}', [DeviceModelController::class, 'update']);
    Route::patch('/{id}', [DeviceModelController::class, 'patch']);
    Route::delete('/{id}', [DeviceModelController::class, 'destroy']);
});

Route::prefix('devices')->group(function(){
    Route::get('/', [DeviceController::class, 'index']);
    Route::get('/{id}', [DeviceController::class, 'show']);
    Route::post('/', [DeviceController::class, 'store']);
    Route::put('/{id}', [DeviceController::class, 'update']);
    Route::patch('/{id}', [DeviceController::class, 'patch']);
    Route::delete('/{id}', [DeviceController::class, 'destroy']);

});

Route::prefix('service-requests')->group(function(){
    Route::get('/', [ServiceRequestController::class, 'index']);
    Route::get('/{id}', [ServiceRequestController::class, 'show']);
    Route::post('/', [ServiceRequestController::class, 'store']);
    Route::put('/{id}', [ServiceRequestController::class, 'update']);
    Route::delete('/{id}', [ServiceRequestController::class, 'destroy']);
});


