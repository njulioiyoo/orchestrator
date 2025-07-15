<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\TenantContext;

class TenantResolveMiddleware
{
    protected TenantContext $tenantContext;

    public function __construct(TenantContext $tenantContext)
    {
        $this->tenantContext = $tenantContext;
    }

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

        // Get tenant from user
        $tenant = $user->tenant;
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'User has no tenant assigned'
            ], 400);
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

        // Set tenant context
        $this->tenantContext->setCurrentTenant($tenant);

        return $next($request);
    }
}