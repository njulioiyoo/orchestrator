<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
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
        try {
            // Check if user is authenticated and is super admin
            $user = $request->user();
            $isSuperAdmin = $user && (
                $user->hasPermissionTo('super_admin') ||
                $user->hasRole('super-admin') ||
                ($user->settings['can_access_all_tenants'] ?? false)
            );
            
            // Try to resolve tenant from request (domain/subdomain)
            $tenant = $this->tenantContext->resolveFromRequest($request);
            
            // If no tenant found from request, try session
            if (!$tenant) {
                $tenant = $this->tenantContext->resolveFromSession();
            }
            
            // If still no tenant, try to get default tenant for super admin
            if (!$tenant && $isSuperAdmin) {
                $tenant = \App\Models\Tenant::where('slug', 'default')->first();
            }

            // Set the tenant in context
            if ($tenant) {
                $this->tenantContext->setTenant($tenant);
                
                // Validate tenant access (skip for super admin)
                if (!$isSuperAdmin && !$this->tenantContext->validateAccess()) {
                    abort(403, 'Tenant access denied or expired.');
                }
            }
        } catch (\Exception $e) {
            // If database is not available or tenant resolution fails,
            // continue without tenant context for development/migration purposes
            if (app()->environment('local') && str_contains($e->getMessage(), 'relation "tenants" does not exist')) {
                // Skip tenant resolution if tenants table doesn't exist yet
                return $next($request);
            }
            throw $e;
        }

        return $next($request);
    }
}
