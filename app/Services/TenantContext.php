<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class TenantContext
{
    protected ?Tenant $tenant = null;

    /**
     * Set the current tenant
     */
    public function setTenant(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
        
        // Store tenant ID in session for persistence
        if ($tenant) {
            session(['current_tenant_id' => $tenant->id]);
        } else {
            session()->forget('current_tenant_id');
        }
    }

    /**
     * Get the current tenant
     */
    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }

    /**
     * Get the current tenant (alias for getTenant)
     */
    public function getCurrentTenant(): ?Tenant
    {
        return $this->getTenant();
    }

    /**
     * Set the current tenant (alias for setTenant)
     */
    public function setCurrentTenant(?Tenant $tenant): void
    {
        $this->setTenant($tenant);
    }

    /**
     * Get the current tenant ID
     */
    public function getId(): ?int
    {
        return $this->tenant?->id;
    }

    /**
     * Check if there's a current tenant
     */
    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    /**
     * Get tenant configuration value
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        if (!$this->hasTenant()) {
            return $default;
        }

        return $this->tenant->getConfigValue($key, $default);
    }

    /**
     * Set tenant configuration value
     */
    public function setConfig(string $key, mixed $value): bool
    {
        if (!$this->hasTenant()) {
            return false;
        }

        $this->tenant->setConfigValue($key, $value);
        return $this->tenant->save();
    }

    /**
     * Clear the current tenant
     */
    public function clear(): void
    {
        $this->tenant = null;
        session()->forget('current_tenant_id');
    }

    /**
     * Resolve tenant from request
     */
    public function resolveFromRequest($request): ?Tenant
    {
        try {
            // Try to resolve from domain
            $host = $request->getHost();
            
            // Check for custom domain
            $tenant = Tenant::active()->byDomain($host)->first();
            
            if (!$tenant) {
                // Check for subdomain
                $subdomain = $this->extractSubdomain($host);
                if ($subdomain) {
                    $tenant = Tenant::active()->bySubdomain($subdomain)->first();
                }
            }

            // Fallback to session if no domain resolution
            if (!$tenant && session('current_tenant_id')) {
                $tenant = Tenant::active()->find(session('current_tenant_id'));
            }

            return $tenant;
        } catch (\Exception $e) {
            // If database connection fails or tenants table doesn't exist, return null
            if (str_contains($e->getMessage(), 'relation "tenants" does not exist') || 
                str_contains($e->getMessage(), 'could not translate host name')) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Resolve tenant from session
     */
    public function resolveFromSession(): ?Tenant
    {
        try {
            $tenantId = session('current_tenant_id');
            
            if (!$tenantId) {
                return null;
            }

            return Cache::remember("tenant.{$tenantId}", 300, function () use ($tenantId) {
                return Tenant::active()->find($tenantId);
            });
        } catch (\Exception $e) {
            // If database connection fails or tenants table doesn't exist, return null
            if (str_contains($e->getMessage(), 'relation "tenants" does not exist') || 
                str_contains($e->getMessage(), 'could not translate host name')) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Extract subdomain from host
     */
    protected function extractSubdomain(string $host): ?string
    {
        // Remove www if present
        $host = preg_replace('/^www\./', '', $host);
        
        // Split by dots
        $parts = explode('.', $host);
        
        // If we have more than 2 parts, the first is likely a subdomain
        if (count($parts) > 2) {
            return $parts[0];
        }

        return null;
    }

    /**
     * Check if user has access to current tenant
     */
    public function userHasAccess($user): bool
    {
        if (!$this->hasTenant()) {
            return true; // No tenant context, allow access
        }

        // Check if user belongs to this tenant
        return $user && $user->tenant_id === $this->getId();
    }

    /**
     * Validate tenant access
     */
    public function validateAccess(): bool
    {
        if (!$this->hasTenant()) {
            return true;
        }

        return $this->tenant->canAccess();
    }

    /**
     * Get tenant branding configuration
     */
    public function getBranding(): array
    {
        if (!$this->hasTenant()) {
            return $this->getDefaultBranding();
        }

        return array_merge(
            $this->getDefaultBranding(),
            $this->getConfig('branding', [])
        );
    }

    /**
     * Get default branding configuration
     */
    protected function getDefaultBranding(): array
    {
        return [
            'app_name' => config('app.name'),
            'logo' => null,
            'primary_color' => '#007bff',
            'secondary_color' => '#6c757d',
            'favicon' => null,
        ];
    }

    /**
     * Get tenant features configuration
     */
    public function getFeatures(): array
    {
        if (!$this->hasTenant()) {
            return $this->getDefaultFeatures();
        }

        return array_merge(
            $this->getDefaultFeatures(),
            $this->getConfig('features', [])
        );
    }

    /**
     * Get default features configuration
     */
    protected function getDefaultFeatures(): array
    {
        return [
            'user_management' => true,
            'role_management' => true,
            'permission_management' => true,
            'menu_management' => true,
            'audit_logs' => true,
            'system_settings' => true,
        ];
    }

    /**
     * Check if feature is enabled
     */
    public function isFeatureEnabled(string $feature): bool
    {
        $features = $this->getFeatures();
        return $features[$feature] ?? false;
    }
}