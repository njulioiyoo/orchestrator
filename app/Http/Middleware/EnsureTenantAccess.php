<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
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

        // If no user, let authentication middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Check if user is super admin
        $isSuperAdmin = $user && (
            $user->hasPermissionTo('super_admin') ||
            $user->hasRole('super-admin') ||
            ($user->settings['can_access_all_tenants'] ?? false)
        );

        // Super admin can access all tenants
        if ($isSuperAdmin) {
            return $next($request);
        }

        // Check if user has access to current tenant
        if (!$this->tenantContext->userHasAccess($user)) {
            abort(403, 'You do not have access to this tenant.');
        }

        return $next($request);
    }
}
