<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class TenantApiCredential extends Model
{
    protected $fillable = [
        'tenant_id',
        'api_key',
        'api_secret',
        'allowed_domains',
        'rate_limits',
        'is_active',
        'expires_at'
    ];

    protected $casts = [
        'allowed_domains' => 'array',
        'rate_limits' => 'array',
        'is_active' => 'boolean',
        'expires_at' => 'datetime'
    ];

    protected $hidden = [
        'api_secret'
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function setApiSecretAttribute($value)
    {
        $this->attributes['api_secret'] = Hash::make($value);
    }

    public function verifyApiSecret($secret): bool
    {
        return Hash::check($secret, $this->api_secret);
    }

    public function isActive(): bool
    {
        return $this->is_active && 
               ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }
}
