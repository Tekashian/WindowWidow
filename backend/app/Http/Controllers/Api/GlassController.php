<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Glass;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GlassController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Glass::query();

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by active
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        } else {
            $query->where('is_active', true);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate or return all
        if ($request->has('per_page')) {
            $perPage = $request->get('per_page', 15);
            $glasses = $query->paginate($perPage);
        } else {
            $glasses = $query->get();
        }
        
        return new JsonResponse($glasses);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'thickness' => 'required|integer|min:1',
            'u_value' => 'required|numeric|min:0',
            'price_per_sqm' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $glass = Glass::create($validated);
        return new JsonResponse($glass, 201);
    }

    public function show(Glass $glass): JsonResponse
    {
        return new JsonResponse($glass);
    }

    public function update(Request $request, Glass $glass): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'type' => 'string',
            'thickness' => 'integer|min:1',
            'u_value' => 'numeric|min:0',
            'price_per_sqm' => 'numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $glass->update($validated);
        return new JsonResponse($glass);
    }

    public function destroy(Glass $glass): JsonResponse
    {
        $glass->delete();
        return new JsonResponse(null, 204);
    }
}
