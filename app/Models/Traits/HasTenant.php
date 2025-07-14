<?php

namespace App\Models\Traits;

use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;

trait HasTenant
{
    /**
     * Boot the trait
     */
    public static function bootHasTenant()
    {
        // Add global scope to filter by current tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantContext = app(TenantContext::class);
            
            if ($tenantContext->hasTenant()) {
                $builder->where('tenant_id', $tenantContext->getId());
            }
        });

        // Auto-assign tenant_id when creating new models
        static::creating(function ($model) {
            $tenantContext = app(TenantContext::class);
            
            if ($tenantContext->hasTenant() && !$model->tenant_id) {
                $model->tenant_id = $tenantContext->getId();
            }
        });
    }

    /**
     * Relationship to tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope to query without tenant filtering
     */
    public function scopeWithoutTenant($query)
    {
        return $query->withoutGlobalScope('tenant');
    }

    /**
     * Scope to query for specific tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->withoutGlobalScope('tenant')->where('tenant_id', $tenantId);
    }

    /**
     * Scope to query for all tenants
     */
    public function scopeAllTenants($query)
    {
        return $query->withoutGlobalScope('tenant');
    }

    /**
     * Check if model belongs to current tenant
     */
    public function belongsToCurrentTenant()
    {
        $tenantContext = app(TenantContext::class);
        
        if (!$tenantContext->hasTenant()) {
            return true; // If no tenant context, allow access
        }

        return $this->tenant_id === $tenantContext->getId();
    }

    /**
     * Check if model belongs to specific tenant
     */
    public function belongsToTenant($tenantId)
    {
        return $this->tenant_id === $tenantId;
    }
}