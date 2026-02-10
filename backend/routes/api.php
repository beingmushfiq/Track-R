<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user()->load('tenant', 'roles');
    });

    // Vehicle Management
    Route::get('/vehicles/auxiliary', [\App\Http\Controllers\VehicleController::class, 'auxiliary']);
    Route::apiResource('vehicles', \App\Http\Controllers\VehicleController::class);

    // Device Management
    Route::get('/devices/auxiliary', [\App\Http\Controllers\DeviceController::class, 'auxiliary']);
    Route::post('/devices/{device}/command', [\App\Http\Controllers\DeviceController::class, 'sendCommand']);
    Route::apiResource('devices', \App\Http\Controllers\DeviceController::class);

    // Alert Rules
    Route::apiResource('alert-rules', \App\Http\Controllers\AlertRuleController::class);

    // Geofences
    Route::apiResource('geofences', \App\Http\Controllers\GeofenceController::class);
});
