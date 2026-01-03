<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Window;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WindowController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Window::with(['profile', 'glass']);

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $windows = $query->paginate($perPage);
        
        return new JsonResponse($windows);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'width' => 'required|integer|min:100',
            'height' => 'required|integer|min:100',
            'profile_id' => 'required|exists:profiles,id',
            'glass_id' => 'required|exists:glasses,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $window = Window::create($validated);
        $window->load(['profile', 'glass']);

        return new JsonResponse($window, 201);
    }

    public function show(Window $window): JsonResponse
    {
        $window->load(['profile', 'glass']);
        return new JsonResponse($window);
    }

    public function update(Request $request, Window $window): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'type' => 'string',
            'width' => 'integer|min:100',
            'height' => 'integer|min:100',
            'profile_id' => 'exists:profiles,id',
            'glass_id' => 'exists:glasses,id',
            'price' => 'numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $window->update($validated);
        $window->load(['profile', 'glass']);

        return new JsonResponse($window);
    }

    public function destroy(Window $window): JsonResponse
    {
        $window->delete();
        return new JsonResponse(null, 204);
    }

    public function updateStock(Request $request, Window $window): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer',
            'operation' => 'required|in:add,subtract'
        ]);

        if ($validated['operation'] === 'add') {
            $window->stock_quantity += $validated['quantity'];
        } else {
            $newQuantity = $window->stock_quantity - $validated['quantity'];
            if ($newQuantity < 0) {
                return new JsonResponse([
                    'message' => 'Insufficient stock quantity'
                ], 400);
            }
            $window->stock_quantity = $newQuantity;
        }

        $window->save();
        $window->load(['profile', 'glass']);

        return new JsonResponse($window);
    }
}
