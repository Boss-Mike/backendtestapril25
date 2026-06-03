<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserInCompany
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && !$request->route('company_id')) {
            // If company_id is not in route, it's valid
            return $next($request);
        }

        if ($request->user() && $request->route('company_id')) {
            if ((int)$request->user()->company_id !== (int)$request->route('company_id')) {
                return response()->json([
                    'message' => 'Unauthorized - Company mismatch'
                ], 403);
            }
        }

        return $next($request);
    }
}
