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
        // Try to resolve tenant from request (domain/subdomain)
        $tenant = $this->tenantContext->resolveFromRequest($request);
        
        // If no tenant found from request, try session
        if (!$tenant) {
            $tenant = $this->tenantContext->resolveFromSession();
        }

        // Set the tenant in context
        if ($tenant) {
            $this->tenantContext->setTenant($tenant);
            
            // Validate tenant access
            if (!$this->tenantContext->validateAccess()) {
                abort(403, 'Tenant access denied or expired.');
            }
        }

        return $next($request);
    }
}
