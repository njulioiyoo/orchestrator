<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait TenantScoped
{
    protected static function bootTenantScoped()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = app('current_tenant_id');
            
            if ($tenantId && $builder->getModel()->getTable() !== 'tenants') {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
            }
        });
        
        static::creating(function ($model) {
            $tenantId = app('current_tenant_id');
            
            if ($tenantId && $model->getTable() !== 'tenants' && !$model->tenant_id) {
                $model->tenant_id = $tenantId;
            }
        });
    }
    
    public function scopeWithoutTenantScope($query)
    {
        return $query->withoutGlobalScope('tenant');
    }
    
    public function scopeForTenant($query, $tenantId)
    {
        return $query->withoutGlobalScope('tenant')->where('tenant_id', $tenantId);
    }
}