<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiUsageLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'api_key',
        'endpoint',
        'method',
        'response_status',
        'response_time_ms',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'response_time_ms' => 'integer',
        'response_status' => 'integer'
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByApiKey($query, $apiKey)
    {
        return $query->where('api_key', $apiKey);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('response_status', $status);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeLastHour($query)
    {
        return $query->where('created_at', '>=', now()->subHour());
    }
}
