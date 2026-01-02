<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WindowController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\GlassController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\ProductionOrderController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

Route::get('/health', function () {
    return ['status' => 'ok', 'timestamp' => now()->toISOString()];
});

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/export-materials', [DashboardController::class, 'exportMaterials']);
    
    // Windows Management
    Route::apiResource('windows', WindowController::class);
    
    // Profiles Management
    Route::apiResource('profiles', ProfileController::class);
    
    // Glass Types Management
    Route::apiResource('glasses', GlassController::class);
    
    // Orders Management
    Route::apiResource('orders', OrderController::class);
    Route::post('orders/{order}/update-status', [OrderController::class, 'updateStatus']);
    
    // Materials & Stock Management
    Route::apiResource('materials', MaterialController::class);
    Route::post('materials/{material}/add-stock', [MaterialController::class, 'addStock']);
    Route::post('materials/{material}/remove-stock', [MaterialController::class, 'removeStock']);
    Route::get('materials/{material}/movements', [MaterialController::class, 'movements']);
    Route::get('low-stock', [MaterialController::class, 'lowStock']);
    
    // Production Orders
    Route::apiResource('production-orders', ProductionOrderController::class);
    Route::post('production-orders/{productionOrder}/start', [ProductionOrderController::class, 'start']);
    Route::post('production-orders/{productionOrder}/complete', [ProductionOrderController::class, 'complete']);
    Route::post('production-orders/{productionOrder}/cancel', [ProductionOrderController::class, 'cancel']);
});
