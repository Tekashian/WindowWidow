<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Glass;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GlassController extends Controller
{
    public function index(): JsonResponse
    {
        $glasses = Glass::where('is_active', true)->get();
        return response()->json($glasses);
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
        return response()->json($glass, 201);
    }

    public function show(Glass $glass): JsonResponse
    {
        return response()->json($glass);
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
        return response()->json($glass);
    }

    public function destroy(Glass $glass): JsonResponse
    {
        $glass->delete();
        return response()->json(null, 204);
    }
}
