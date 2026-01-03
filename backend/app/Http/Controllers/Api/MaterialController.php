<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\StockMovement;
use App\Events\LowStockAlert;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MaterialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Material::with('stockMovements');

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('supplier', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        } else {
            $query->where('is_active', true);
        }

        // Filter by low stock
        if ($request->boolean('low_stock')) {
            $query->whereColumn('current_stock', '<=', 'min_stock');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $materials = $query->paginate($perPage);
        
        return new JsonResponse($materials);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:profil,szyba,okucie,uszczelka,inne',
            'unit' => 'required|string',
            'current_stock' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'price_per_unit' => 'required|numeric|min:0',
            'supplier' => 'nullable|string',
        ]);

        $material = Material::create($validated);

        return new JsonResponse($material, 201);
    }

    public function show(Material $material): JsonResponse
    {
        $material->load('stockMovements.user');
        return new JsonResponse($material);
    }

    public function update(Request $request, Material $material): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'type' => 'in:profil,szyba,okucie,uszczelka,inne',
            'unit' => 'string',
            'min_stock' => 'numeric|min:0',
            'price_per_unit' => 'numeric|min:0',
            'supplier' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $material->update($validated);

        return new JsonResponse($material);
    }

    public function destroy(Material $material): JsonResponse
    {
        $material->delete();
        return new JsonResponse(null, 204);
    }

    public function addStock(Request $request, Material $material): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
        ]);

        try {
            $material->addStock($validated['quantity'], $validated['reason'] ?? null);
            
            return new JsonResponse([
                'message' => 'Stan magazynowy zaktualizowany',
                'material' => $material->fresh(),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function removeStock(Request $request, Material $material): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
        ]);

        try {
            $material->removeStock($validated['quantity'], $validated['reason'] ?? null);
            
            // Sprawdź czy nie jest niski stan
            if ($material->isLowStock()) {
                event(new LowStockAlert($material));
            }
            
            return new JsonResponse([
                'message' => 'Stan magazynowy zaktualizowany',
                'material' => $material->fresh(),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function movements(Material $material): JsonResponse
    {
        $movements = $material->stockMovements()
            ->with('user')
            ->latest()
            ->paginate(50);
        
        return new JsonResponse($movements);
    }

    public function lowStock(): JsonResponse
    {
        $materials = Material::all()->filter->isLowStock()->values();
        
        return new JsonResponse($materials);
    }
}
