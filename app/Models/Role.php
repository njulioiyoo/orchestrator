<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use App\Traits\EncryptedRouteKey;
use App\Models\Traits\HasTenant;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Role extends SpatieRole implements Auditable
{
    use EncryptedRouteKey, HasTenant, AuditableTrait;
}
