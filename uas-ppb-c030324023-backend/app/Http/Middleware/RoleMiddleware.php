<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! $request->user() || $request->user()->role !== $role) {
            // ponytail: web routes get an HTML 403, API routes the JSON error shape.
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses.',
                    'errors' => [],
                    'code' => 'FORBIDDEN',
                ], 403);
            }

            abort(403, 'Anda tidak memiliki akses.');
        }

        return $next($request);
    }
}
