<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProximityController;

Route::get('/', function () {
    return view('dashboard.index');
});

// Proximity API routes
Route::post('/api/proximity/check', [ProximityController::class, 'checkProximity']);
Route::post('/api/proximity/batch', [ProximityController::class, 'batchProximityCheck']);

// Dashboard routes
Route::get('/dashboard', function () {
    return redirect()->route('dashboard.map');
})->name('dashboard');
Route::get('/dashboard/map', [ProximityController::class, 'showMapDashboard'])->name('dashboard.map');
Route::get('/dashboard/heatmap', [ProximityController::class, 'showHeatmapDashboard'])->name('dashboard.heatmap');
Route::get('/dashboard/realtime', [ProximityController::class, 'showRealTimeDashboard'])->name('dashboard.realtime');

// Statistics and analytics routes
Route::get('/api/stats/alerts', [ProximityController::class, 'getAlertStats']);
Route::get('/api/stats/delivery/{deliveryId}', [ProximityController::class, 'getDeliveryStats']);

// Notification routes
Route::middleware(['auth'])->group(function () {
    Route::get('/api/notifications/history', [ProximityController::class, 'getNotificationHistory']);
    Route::post('/api/notifications/mark-read', [ProximityController::class, 'markNotificationsAsRead']);
});

// Legacy routes (keeping for compatibility)
Route::get('/proximity-form', function () {
    return view('dashboard.form');
});
Route::post('/check-proximity', [ProximityController::class, 'checkProximity'])->name('check.proximity');
Route::get('/proximity-alerts', function () {
    return redirect()->route('dashboard.map');
})->name('proximity.alerts');