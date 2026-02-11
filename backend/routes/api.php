<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
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

    // Tracking
    Route::prefix('tracking')->group(function () {
        Route::get('/live', [\App\Http\Controllers\TrackingController::class, 'live']);
        Route::get('/vehicle/{vehicleId}/last-position', [\App\Http\Controllers\TrackingController::class, 'lastPosition']);
        Route::get('/vehicle/{vehicleId}/playback', [\App\Http\Controllers\TrackingController::class, 'playback']);
        Route::get('/vehicle/{vehicleId}/route-stats', [\App\Http\Controllers\TrackingController::class, 'routeStats']);
    });

    // Trips
    Route::get('/trips', [\App\Http\Controllers\TripController::class, 'index']);
    Route::get('/trips/{tripId}', [\App\Http\Controllers\TripController::class, 'show']);
    Route::get('/trips/{tripId}/stops', [\App\Http\Controllers\TripController::class, 'stops']);

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/daily-summary', [\App\Http\Controllers\ReportController::class, 'dailySummary']);
        Route::post('/daily-summary/export', [\App\Http\Controllers\ReportController::class, 'export']);
        
        Route::get('/subscriptions', [\App\Http\Controllers\ReportController::class, 'index']);
        Route::post('/subscriptions', [\App\Http\Controllers\ReportController::class, 'store']);
        Route::delete('/subscriptions/{id}', [\App\Http\Controllers\ReportController::class, 'destroy']);
    });

    // Geofences
    Route::prefix('geofences')->group(function () {
        Route::get('/', [\App\Http\Controllers\GeofenceController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\GeofenceController::class, 'store']);
        Route::get('/{geofence}', [\App\Http\Controllers\GeofenceController::class, 'show']);
        Route::put('/{geofence}', [\App\Http\Controllers\GeofenceController::class, 'update']);
        Route::delete('/{geofence}', [\App\Http\Controllers\GeofenceController::class, 'destroy']);
        Route::get('/{geofence}/events', [\App\Http\Controllers\GeofenceController::class, 'events']);
    });

    // Alerts
    Route::prefix('alerts')->group(function () {
        Route::get('/', [\App\Http\Controllers\AlertController::class, 'index']);
        Route::get('/{id}', [\App\Http\Controllers\AlertController::class, 'show']);
        Route::patch('/{id}/read', [\App\Http\Controllers\AlertController::class, 'markAsRead']);
        Route::post('/mark-read', [\App\Http\Controllers\AlertController::class, 'markMultipleAsRead']);
        Route::delete('/{id}', [\App\Http\Controllers\AlertController::class, 'destroy']);
    });

    // Alert Rules
    Route::prefix('alert-rules')->group(function () {
        Route::get('/', [\App\Http\Controllers\AlertController::class, 'listRules']);
        Route::post('/', [\App\Http\Controllers\AlertController::class, 'storeRule']);
        Route::get('/{id}', [\App\Http\Controllers\AlertController::class, 'showRule']);
        Route::put('/{id}', [\App\Http\Controllers\AlertController::class, 'updateRule']);
        Route::delete('/{id}', [\App\Http\Controllers\AlertController::class, 'destroyRule']);
    });

    // Device Commands (Engine Control)
    Route::prefix('devices/{device}')->group(function () {
        Route::get('/commands', [\App\Http\Controllers\DeviceCommandController::class, 'index']);
        Route::post('/commands', [\App\Http\Controllers\DeviceCommandController::class, 'store']);
        Route::post('/lock', [\App\Http\Controllers\DeviceCommandController::class, 'lockEngine']);
        Route::post('/unlock', [\App\Http\Controllers\DeviceCommandController::class, 'unlockEngine']);
    });

    // Panic Events
    Route::prefix('panic-events')->group(function () {
        Route::get('/', [\App\Http\Controllers\PanicEventController::class, 'index']);
        Route::get('/statistics', [\App\Http\Controllers\PanicEventController::class, 'statistics']);
        Route::get('/{panicEvent}', [\App\Http\Controllers\PanicEventController::class, 'show']);
        Route::post('/{panicEvent}/resolve', [\App\Http\Controllers\PanicEventController::class, 'resolve']);
    });

    // Diagnostics
    Route::prefix('diagnostics')->group(function () {
        Route::get('/summary', [\App\Http\Controllers\DiagnosticsController::class, 'fleetSummary']);
        Route::post('/codes/{code}/clear', [\App\Http\Controllers\DiagnosticsController::class, 'clearCode']);
    });

    // Vehicle Diagnostics
    Route::prefix('vehicles/{vehicle}')->group(function () {
        Route::get('/diagnostics', [\App\Http\Controllers\DiagnosticsController::class, 'index']);
        Route::get('/health-score', [\App\Http\Controllers\DiagnosticsController::class, 'healthScore']);
        Route::get('/diagnostic-codes', [\App\Http\Controllers\DiagnosticsController::class, 'diagnosticCodes']);
    });

    // Billing
    Route::prefix('billing')->group(function () {
        Route::get('/plans', [\App\Http\Controllers\BillingController::class, 'plans']);
        Route::get('/subscription', [\App\Http\Controllers\BillingController::class, 'subscription']);
        Route::post('/subscribe', [\App\Http\Controllers\BillingController::class, 'subscribe']);
        Route::post('/cancel', [\App\Http\Controllers\BillingController::class, 'cancel']);
        Route::get('/invoices', [\App\Http\Controllers\BillingController::class, 'invoices']);
    });
});
