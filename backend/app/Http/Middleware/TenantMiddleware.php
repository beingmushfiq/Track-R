<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Ensures that the authenticated user's tenant context is set
     * and enforced throughout the request lifecycle.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to authenticated requests
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Ensure user has a tenant
        if (!$user->tenant_id) {
            return response()->json([
                'message' => 'User is not associated with any tenant.'
            ], 403);
        }

        // Set tenant context in config for the request lifecycle
        // This can be accessed throughout the application
        config(['app.current_tenant_id' => $user->tenant_id]);

        // Share tenant with views (if using Blade)
        view()->share('currentTenant', $user->tenant);

        return $next($request);
    }
}
