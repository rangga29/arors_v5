<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ExternalApiBearerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token || $token !== env('EXTERNAL_API_TOKEN', 'rsck-external-api-token-2025')) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Unauthorized. Invalid or missing Bearer token.',
            ], 401);
        }

        return $next($request);
    }
}
