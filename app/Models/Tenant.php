<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\EncryptedRouteKey;

class Tenant extends Model
{
    use HasFactory, EncryptedRouteKey;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'subdomain',
        'config',
        'is_active',
        'expires_at',
        'tenant_type',
        'allow_web_login',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'allow_web_login' => 'boolean',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDomain($query, $domain)
    {
        return $query->where('domain', $domain);
    }

    public function scopeBySubdomain($query, $subdomain)
    {
        return $query->where('subdomain', $subdomain);
    }

    // Helper methods
    public function getConfigValue($key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    public function setConfigValue($key, $value)
    {
        $config = $this->config ?? [];
        data_set($config, $key, $value);
        $this->config = $config;
        return $this;
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function canAccess()
    {
        return $this->is_active && !$this->isExpired();
    }

    // Tenant Type Helper Methods
    public function isRegularTenant()
    {
        return $this->tenant_type === 'regular';
    }

    public function isApiOnlyTenant()
    {
        return $this->tenant_type === 'api_only';
    }

    public function canWebLogin()
    {
        return $this->allow_web_login && $this->isRegularTenant();
    }

    // Relationships for API
    public function apiCredentials()
    {
        return $this->hasMany(TenantApiCredential::class);
    }

    public function apiUsageLogs()
    {
        return $this->hasMany(ApiUsageLog::class);
    }

    // Scopes
    public function scopeRegular($query)
    {
        return $query->where('tenant_type', 'regular');
    }

    public function scopeApiOnly($query)
    {
        return $query->where('tenant_type', 'api_only');
    }

    public function scopeCanWebLogin($query)
    {
        return $query->where('allow_web_login', true)
                    ->where('tenant_type', 'regular');
    }
}
