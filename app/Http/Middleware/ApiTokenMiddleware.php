<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $providedToken = $request->header('Authorization');

        // Compare the provided token with the expected system token
        if ($providedToken !== 'Bearer RlfzSIcrRBD6qGtENmKCiKbiEFOMBQ1Ix4fy0hsR9Ok=') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
