<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use App\Services\ProductionOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProductionOrderController extends Controller
{
    protected ProductionOrderService $service;

    public function __construct(ProductionOrderService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        $orders = ProductionOrder::with(['items.window', 'assignedUser'])
            ->latest()
            ->get();
        
        return new JsonResponse($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'priority' => 'required|in:niska,normalna,wysoka,pilne',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.window_id' => 'required|exists:windows,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $productionOrder = $this->service->createProductionOrder($validated);
            
            return new JsonResponse($productionOrder, 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function show(ProductionOrder $productionOrder): JsonResponse
    {
        $productionOrder->load(['items.window', 'assignedUser', 'stockMovements.material']);
        return new JsonResponse($productionOrder);
    }

    public function update(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        $validated = $request->validate([
            'priority' => 'in:niska,normalna,wysoka,pilne',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $productionOrder->update($validated);
        
        return new JsonResponse($productionOrder->fresh());
    }

    public function destroy(ProductionOrder $productionOrder): JsonResponse
    {
        $productionOrder->delete();
        return new JsonResponse(null, 204);
    }

    public function startProduction(int $id): JsonResponse
    {
        try {
            Log::info('Starting production for order', ['order_id' => $id]);
            
            $productionOrder = ProductionOrder::with(['items.window'])->findOrFail($id);
            
            Log::info('Production order loaded', [
                'order_id' => $id,
                'status' => $productionOrder->status,
                'items_count' => $productionOrder->items->count()
            ]);
            
            $this->service->startProduction($productionOrder);
            
            Log::info('Production started successfully', ['order_id' => $id]);
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Produkcja rozpoczęta pomyślnie',
                'order' => $productionOrder->fresh(['items.window', 'assignedUser']),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Production order not found', ['order_id' => $id]);
            return new JsonResponse([
                'success' => false,
                'error' => 'Zlecenie produkcyjne nie znalezione'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error starting production', [
                'order_id' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
                'details' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null
            ], 500);
        }
    }

    public function complete(int $id): JsonResponse
    {
        try {
            $productionOrder = ProductionOrder::with(['items.window'])->findOrFail($id);
            $this->service->completeProduction($productionOrder);
            
            return new JsonResponse([
                'message' => 'Produkcja zakończona',
                'order' => $productionOrder->fresh(['items.window', 'assignedUser']),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function cancel(int $id): JsonResponse
    {
        $productionOrder = ProductionOrder::findOrFail($id);
        $productionOrder->update(['status' => 'anulowane']);
        
        return new JsonResponse([
            'message' => 'Zlecenie anulowane',
            'order' => $productionOrder->fresh(),
        ]);
    }

    public function statistics(): JsonResponse
    {
        $stats = [
            'total' => ProductionOrder::count(),
            'nowe' => ProductionOrder::where('status', 'nowe')->count(),
            'w_trakcie' => ProductionOrder::where('status', 'w_trakcie')->count(),
            'zakonczone' => ProductionOrder::where('status', 'zakonczone')->count(),
        ];
        
        return new JsonResponse($stats);
    }

    public function confirmOrder(int $id): JsonResponse
    {
        $productionOrder = ProductionOrder::findOrFail($id);
        $productionOrder->update(['confirmed_by_production' => true, 'confirmed_at' => now()]);
        
        return new JsonResponse([
            'message' => 'Zlecenie potwierdzone',
            'order' => $productionOrder->fresh(),
        ]);
    }

    public function reportDelay(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'revised_completion_at' => 'required|date',
        ]);

        $productionOrder = ProductionOrder::findOrFail($id);
        $productionOrder->reportDelay($validated['reason'], $validated['revised_completion_at']);
        
        return new JsonResponse([
            'message' => 'Opóźnienie zgłoszone',
            'order' => $productionOrder->fresh(),
        ]);
    }

    public function updateProgress(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $productionOrder = ProductionOrder::findOrFail($id);
        $productionOrder->updateProgress($validated['status'], $validated['notes'] ?? null);
        
        return new JsonResponse([
            'message' => 'Postęp zaktualizowany',
            'order' => $productionOrder->fresh(),
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        $productionOrder = ProductionOrder::findOrFail($id);
        $productionOrder->update(['status' => $validated['status']]);
        
        return new JsonResponse([
            'message' => 'Status zaktualizowany',
            'order' => $productionOrder->fresh(),
        ]);
    }

    public function reportIssue(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'severity' => 'required|in:niska,srednia,wysoka,krytyczna',
        ]);

        $productionOrder = ProductionOrder::findOrFail($id);
        
        \App\Models\ProductionIssue::create([
            'production_order_id' => $productionOrder->id,
            'description' => $validated['description'],
            'severity' => $validated['severity'],
            'status' => 'otwarte',
        ]);
        
        return new JsonResponse([
            'message' => 'Problem zgłoszony',
        ]);
    }

    public function createBatch(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'batch_number' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $productionOrder = ProductionOrder::findOrFail($id);
        
        \App\Models\ProductionBatch::create([
            'production_order_id' => $productionOrder->id,
            'batch_number' => $validated['batch_number'],
            'quantity' => $validated['quantity'],
            'status' => 'w_trakcie',
        ]);
        
        return new JsonResponse([
            'message' => 'Partia utworzona',
        ]);
    }

    public function shipToWarehouse(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'delivery_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $productionOrder = ProductionOrder::findOrFail($id);
        
        \App\Models\WarehouseDelivery::create([
            'production_order_id' => $productionOrder->id,
            'delivery_date' => $validated['delivery_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'zaplanowana',
        ]);
        
        return new JsonResponse([
            'message' => 'Dostawa do magazynu zaplanowana',
        ]);
    }
}
