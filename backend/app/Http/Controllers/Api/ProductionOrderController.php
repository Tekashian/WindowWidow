<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use App\Services\ProductionOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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

    public function start(ProductionOrder $productionOrder): JsonResponse
    {
        try {
            $this->service->startProduction($productionOrder);
            
            return new JsonResponse([
                'message' => 'Produkcja rozpoczęta',
                'order' => $productionOrder->fresh(['items.window']),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function complete(ProductionOrder $productionOrder): JsonResponse
    {
        try {
            $this->service->completeProduction($productionOrder);
            
            return new JsonResponse([
                'message' => 'Produkcja zakończona',
                'order' => $productionOrder->fresh(['items.window']),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function cancel(ProductionOrder $productionOrder): JsonResponse
    {
        $productionOrder->update(['status' => 'anulowane']);
        
        return new JsonResponse([
            'message' => 'Zlecenie anulowane',
            'order' => $productionOrder->fresh(),
        ]);
    }
}
