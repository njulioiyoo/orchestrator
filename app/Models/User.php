<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\EncryptedRouteKey;
use App\Models\Traits\HasTenant;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements Auditable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, EncryptedRouteKey, HasTenant, AuditableTrait, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'is_active',
        'profile',
        'user_permissions',
        'settings',
        'last_login_at',
    ];

    protected $auditExclude = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'profile' => 'array',
            'user_permissions' => 'array',
            'settings' => 'array',
            'last_login_at' => 'datetime',
        ];
    }

    public function getAllPermissionsAttribute()
    {
        return $this->getAllPermissions()->pluck('name')->toArray();
    }

    public function getRoleNamesAttribute()
    {
        return $this->getRoleNames()->toArray();
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
