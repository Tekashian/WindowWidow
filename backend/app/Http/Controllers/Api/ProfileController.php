<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Profile::query();

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('manufacturer', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
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
            $profiles = $query->paginate($perPage);
        } else {
            $profiles = $query->get();
        }
        
        return new JsonResponse($profiles);
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
        return new JsonResponse($profile, 201);
    }

    public function show(Profile $profile): JsonResponse
    {
        return new JsonResponse($profile);
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
        return new JsonResponse($profile);
    }

    public function destroy(Profile $profile): JsonResponse
    {
        $profile->delete();
        return new JsonResponse(null, 204);
    }
}
