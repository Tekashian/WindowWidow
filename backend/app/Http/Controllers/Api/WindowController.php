<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Window;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WindowController extends Controller
{
    public function index(): JsonResponse
    {
        $windows = Window::with(['profile', 'glass'])
            ->get();
        
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

        return response()->json($window, 201);
    }

    public function show(Window $window): JsonResponse
    {
        $window->load(['profile', 'glass']);
        return response()->json($window);
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

        return response()->json($window);
    }

    public function destroy(Window $window): JsonResponse
    {
        $window->delete();
        return response()->json(null, 204);
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
                return response()->json([
                    'message' => 'Insufficient stock quantity'
                ], 400);
            }
            $window->stock_quantity = $newQuantity;
        }

        $window->save();
        $window->load(['profile', 'glass']);

        return response()->json($window);
    }
}
