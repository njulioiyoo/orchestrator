<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\Traits\HasTenant;

class SystemSetting extends Model implements Auditable
{
    use HasFactory, HasTenant, AuditableTrait;

    protected $fillable = [
        'key',
        'name',
        'value',
        'type',
        'description',
        'group',
        'options',
        'is_public',
        'sort_order',
        'tenant_id'
    ];

    protected $casts = [
        'options' => 'array',
        'is_public' => 'boolean'
    ];

    /**
     * Attributes to include in the Audit.
     *
     * @var array
     */
    protected $auditInclude = [
        'key',
        'name', 
        'value',
        'type',
        'description',
        'group',
        'options',
        'is_public',
        'sort_order',
        'tenant_id'
    ];

    /**
     * Generating tags for each model audited.
     *
     * @return array
     */
    public function generateTags(): array
    {
        return ['system_settings'];
    }

    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return match($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'number' => is_numeric($setting->value) ? (float)$setting->value : $default,
            'json' => json_decode($setting->value, true),
            default => $setting->value
        };
    }

    public static function set($key, $value)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return false;
        }

        $processedValue = match($setting->type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string)$value
        };

        return $setting->update(['value' => $processedValue]);
    }

    public static function getByGroup($group)
    {
        return static::where('group', $group)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get();
    }

    public static function getAllGrouped()
    {
        return static::orderBy('group')
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get()
                    ->groupBy('group');
    }
}
