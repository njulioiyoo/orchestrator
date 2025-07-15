<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;

class TenantAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Get tenant from route parameter
        $tenant = $request->route('tenant');
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not specified'
            ], 400);
        }

        // If tenant is passed as ID, convert to model
        if (is_numeric($tenant)) {
            $tenant = Tenant::find($tenant);
        }

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }

        // Check if user belongs to this tenant
        if ($user->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied to this tenant'
            ], 403);
        }

        // Check if tenant is active
        if (!$tenant->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant is inactive'
            ], 403);
        }

        // Check if tenant is expired
        if ($tenant->expires_at && $tenant->expires_at < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant subscription has expired'
            ], 403);
        }

        return $next($request);
    }
}