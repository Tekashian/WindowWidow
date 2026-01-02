<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WindowController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\GlassController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\ProductionOrderController as ApiProductionOrderController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ProductionOrderController;
use App\Http\Controllers\ProductionBatchController;
use App\Http\Controllers\ProductionIssueController;
use App\Http\Controllers\WarehouseDeliveryController;

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
    Route::post('windows/{window}/update-stock', [WindowController::class, 'updateStock']);
    
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
    
    // Old Production Orders (legacy)
    Route::apiResource('production-orders-old', ApiProductionOrderController::class);
    Route::post('production-orders-old/{productionOrder}/start', [ApiProductionOrderController::class, 'start']);
    Route::post('production-orders-old/{productionOrder}/complete', [ApiProductionOrderController::class, 'complete']);
    Route::post('production-orders-old/{productionOrder}/cancel', [ApiProductionOrderController::class, 'cancel']);
    
    // New Production System
    Route::prefix('production')->group(function () {
        // Production Orders
        Route::get('orders', [ProductionOrderController::class, 'index']);
        Route::post('orders', [ProductionOrderController::class, 'store']);
        Route::get('orders/statistics', [ProductionOrderController::class, 'statistics']);
        Route::get('orders/{id}', [ProductionOrderController::class, 'show']);
        Route::put('orders/{id}', [ProductionOrderController::class, 'update']);
        Route::delete('orders/{id}', [ProductionOrderController::class, 'destroy']);
        Route::post('orders/{id}/start', [ProductionOrderController::class, 'startProduction']);
        Route::post('orders/{id}/update-status', [ProductionOrderController::class, 'updateStatus']);
        Route::post('orders/{id}/report-issue', [ProductionOrderController::class, 'reportIssue']);
        Route::post('orders/{id}/create-batch', [ProductionOrderController::class, 'createBatch']);
        Route::post('orders/{id}/ship-to-warehouse', [ProductionOrderController::class, 'shipToWarehouse']);
        
        // Production Batches
        Route::get('batches', [ProductionBatchController::class, 'index']);
        Route::get('batches/{id}', [ProductionBatchController::class, 'show']);
        Route::post('batches/{id}/update-status', [ProductionBatchController::class, 'updateStatus']);
        
        // Production Issues
        Route::get('issues', [ProductionIssueController::class, 'index']);
        Route::get('issues/statistics', [ProductionIssueController::class, 'statistics']);
        Route::get('issues/{id}', [ProductionIssueController::class, 'show']);
        Route::post('issues/{id}/update-status', [ProductionIssueController::class, 'updateStatus']);
        Route::post('issues/{id}/resolve', [ProductionIssueController::class, 'resolve']);
    });
    
    // Warehouse System
    Route::prefix('warehouse')->group(function () {
        Route::get('deliveries', [WarehouseDeliveryController::class, 'index']);
        Route::get('deliveries/statistics', [WarehouseDeliveryController::class, 'statistics']);
        Route::get('deliveries/{id}', [WarehouseDeliveryController::class, 'show']);
        Route::post('deliveries/{id}/ship', [WarehouseDeliveryController::class, 'ship']);
        Route::post('deliveries/{id}/receive', [WarehouseDeliveryController::class, 'receive']);
        Route::post('deliveries/{id}/reject', [WarehouseDeliveryController::class, 'reject']);
    });
});
