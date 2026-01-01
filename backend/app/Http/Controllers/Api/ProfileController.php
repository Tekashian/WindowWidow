<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function index(): JsonResponse
    {
        $profiles = Profile::where('is_active', true)->get();
        return response()->json($profiles);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'manufacturer' => 'required|string|max:255',
            'type' => 'required|string',
            'material' => 'required|string',
            'color' => 'required|string',
            'price_per_meter' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $profile = Profile::create($validated);
        return response()->json($profile, 201);
    }

    public function show(Profile $profile): JsonResponse
    {
        return response()->json($profile);
    }

    public function update(Request $request, Profile $profile): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'manufacturer' => 'string|max:255',
            'type' => 'string',
            'material' => 'string',
            'color' => 'string',
            'price_per_meter' => 'numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $profile->update($validated);
        return response()->json($profile);
    }

    public function destroy(Profile $profile): JsonResponse
    {
        $profile->delete();
        return response()->json(null, 204);
    }
}
