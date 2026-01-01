<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Nieautoryzowany'], 401);
        }

        if (!in_array($request->user()->role, $roles)) {
            return response()->json([
                'message' => 'Brak uprawnień do wykonania tej operacji'
            ], 403);
        }

        return $next($request);
    }
}
