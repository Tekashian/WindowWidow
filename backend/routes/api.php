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
use App\Http\Controllers\Api\ImageUploadController;
use App\Http\Controllers\NotificationController;

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
    
    // Dashboard (all roles)
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/export-materials', [DashboardController::class, 'exportMaterials']);
    
    // Windows Management (admin only for create/update/delete)
    Route::get('windows', [WindowController::class, 'index']);
    Route::get('windows/{window}', [WindowController::class, 'show']);
    Route::middleware('role:admin')->group(function () {
        Route::post('windows', [WindowController::class, 'store']);
        Route::put('windows/{window}', [WindowController::class, 'update']);
        Route::delete('windows/{window}', [WindowController::class, 'destroy']);
        Route::post('windows/{window}/update-stock', [WindowController::class, 'updateStock']);
    });
    
    // Profiles Management (admin only for create/update/delete)
    Route::get('profiles', [ProfileController::class, 'index']);
    Route::get('profiles/{profile}', [ProfileController::class, 'show']);
    Route::middleware('role:admin')->group(function () {
        Route::post('profiles', [ProfileController::class, 'store']);
        Route::put('profiles/{profile}', [ProfileController::class, 'update']);
        Route::delete('profiles/{profile}', [ProfileController::class, 'destroy']);
    });
    
    // Glass Types Management (admin only for create/update/delete)
    Route::get('glasses', [GlassController::class, 'index']);
    Route::get('glasses/{glass}', [GlassController::class, 'show']);
    Route::middleware('role:admin')->group(function () {
        Route::post('glasses', [GlassController::class, 'store']);
        Route::put('glasses/{glass}', [GlassController::class, 'update']);
        Route::delete('glasses/{glass}', [GlassController::class, 'destroy']);
    });
    
    // Orders Management (all roles can view, admin can manage)
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::middleware('role:admin')->group(function () {
        Route::post('orders', [OrderController::class, 'store']);
        Route::put('orders/{order}', [OrderController::class, 'update']);
        Route::delete('orders/{order}', [OrderController::class, 'destroy']);
        Route::post('orders/{order}/update-status', [OrderController::class, 'updateStatus']);
    });
    
    // Materials & Stock Management (warehouse and admin)
    Route::middleware('role:admin,warehouse')->group(function () {
        Route::apiResource('materials', MaterialController::class);
        Route::post('materials/{material}/add-stock', [MaterialController::class, 'addStock']);
        Route::post('materials/{material}/remove-stock', [MaterialController::class, 'removeStock']);
    });
    Route::get('materials/{material}/movements', [MaterialController::class, 'movements']);
    Route::get('low-stock', [MaterialController::class, 'lowStock']);
    
    // Old Production Orders (legacy - admin only)
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('production-orders-old', ApiProductionOrderController::class);
        Route::post('production-orders-old/{productionOrder}/start', [ApiProductionOrderController::class, 'start']);
        Route::post('production-orders-old/{productionOrder}/complete', [ApiProductionOrderController::class, 'complete']);
        Route::post('production-orders-old/{productionOrder}/cancel', [ApiProductionOrderController::class, 'cancel']);
    });
    
    // New Production System
    Route::prefix('production')->group(function () {
        // Production helpers (available to all authenticated users)
        Route::get('products', [ProductionOrderController::class, 'getProducts']);
        Route::get('company-settings', [ProductionOrderController::class, 'getCompanySettings']);
        
        // Production Orders (production and admin roles only)
        Route::middleware('role:production,admin')->group(function () {
            Route::get('orders', [ProductionOrderController::class, 'index']);
            Route::post('orders', [ProductionOrderController::class, 'store']);
            Route::get('orders/statistics', [ProductionOrderController::class, 'statistics']);
            Route::get('orders/{id}', [ProductionOrderController::class, 'show']);
            Route::put('orders/{id}', [ProductionOrderController::class, 'update']);
            Route::delete('orders/{id}', [ProductionOrderController::class, 'destroy']);
            Route::post('orders/{id}/start', [ProductionOrderController::class, 'startProduction']);
            Route::post('orders/{id}/confirm', [ProductionOrderController::class, 'confirmOrder']);
            Route::post('orders/{id}/report-delay', [ProductionOrderController::class, 'reportDelay']);
            Route::post('orders/{id}/update-progress', [ProductionOrderController::class, 'updateProgress']);
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
    });
    
    // Warehouse System (warehouse and admin roles)
    Route::prefix('warehouse')->middleware('role:warehouse,admin')->group(function () {
        Route::get('deliveries', [WarehouseDeliveryController::class, 'index']);
        Route::get('deliveries/statistics', [WarehouseDeliveryController::class, 'statistics']);
        Route::get('deliveries/{id}', [WarehouseDeliveryController::class, 'show']);
        Route::post('deliveries/{id}/ship', [WarehouseDeliveryController::class, 'ship']);
        Route::post('deliveries/{id}/receive', [WarehouseDeliveryController::class, 'receive']);
        Route::post('deliveries/{id}/reject', [WarehouseDeliveryController::class, 'reject']);
    });
    
    // Image Upload (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::post('upload/image', [ImageUploadController::class, 'upload']);
        Route::delete('upload/image', [ImageUploadController::class, 'delete']);
    });
    
    // Notifications (all authenticated users)
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/read/all', [NotificationController::class, 'deleteAllRead']);
    });
});
