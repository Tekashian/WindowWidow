<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return new JsonResponse([
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$user->is_active) {
            return new JsonResponse([
                'message' => 'Account inactive'
            ], 403);
        }

        // Delete all previous tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth-token', ['*'], now()->addDays(30))->plainTextToken;

        return new JsonResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ],
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return new JsonResponse([
            'message' => 'Successfully logged out'
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return new JsonResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
        ]);
    }
}
