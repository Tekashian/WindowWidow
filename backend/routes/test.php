<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Test endpoint to check auth
Route::get('/test-auth', function (Request $request) {
    $user = $request->user();
    
    return response()->json([
        'authenticated' => $user !== null,
        'user' => $user ? [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ] : null,
        'headers' => [
            'authorization' => $request->header('Authorization'),
        ],
        'token_in_cookie' => $request->cookie('sanctum_token'),
    ]);
})->middleware(['auth:sanctum']);
