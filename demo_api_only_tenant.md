# Demo: API-Only Tenant Implementation

## Overview
This demo shows how to create API-only tenants that cannot login via web interface but can only access through external API endpoints.

## Step-by-Step Demo

### 1. Create API Credentials and Set Tenant as API-Only

```bash
# Generate API credentials with make_api_only=true
curl -X POST http://localhost:8982/api/v1/tenants/2/api-credentials \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{
    "allowed_domains": ["*"],
    "rate_limits": {
      "requests_per_minute": 100,
      "requests_per_hour": 1000
    },
    "make_api_only": true
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "API credentials generated successfully",
  "data": {
    "api_key": "ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "api_secret": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "tenant_id": 2,
    "credential": {
      "id": 1,
      "tenant_id": 2,
      "api_key": "ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
      "allowed_domains": ["*"],
      "rate_limits": {
        "requests_per_minute": 100,
        "requests_per_hour": 1000
      },
      "is_active": true,
      "expires_at": null,
      "created_at": "2025-07-16T14:30:00.000000Z",
      "updated_at": "2025-07-16T14:30:00.000000Z",
      "tenant": {
        "id": 2,
        "name": "Rental Sukses Mandiri",
        "slug": "rental-sukses"
      }
    }
  }
}
```

### 2. Test Web Login Blocking

Try to login via web interface with user from API-only tenant:

```bash
# This should fail with API-only tenant
curl -X POST http://localhost:8982/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "staff@rentalsukses.com",
    "password": "password123"
  }'
```

**Expected Response:**
```json
{
  "errors": {
    "email": [
      "This tenant is configured for API-only access. Web login is not permitted."
    ]
  }
}
```

### 3. Test External API Login (Should Work)

```bash
# This should work for API-only tenant
curl -X POST http://localhost:8982/api/external/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "X-API-Key: ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-Tenant-ID: 2" \
  -d '{
    "email": "staff@rentalsukses.com",
    "password": "password123"
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "user": {
      "id": 2,
      "name": "Staff User",
      "email": "staff@rentalsukses.com",
      "tenant_id": 2
    },
    "tenant": {
      "id": 2,
      "name": "Rental Sukses Mandiri",
      "slug": "rental-sukses"
    }
  }
}
```

### 4. Test External API Endpoints

```bash
# Get tenant info
curl -X GET http://localhost:8982/api/external/v1/tenant/info \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-API-Key: ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-Tenant-ID: 2"

# Get tenant users
curl -X GET http://localhost:8982/api/external/v1/users \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-API-Key: ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-Tenant-ID: 2"
```

## Manual Testing via PHP Tinker

### Create API-Only Tenant
```php
// Connect to container
docker-compose exec orchestrator php artisan tinker

// Make tenant API-only
$tenant = App\Models\Tenant::find(2);
$tenant->update([
    'tenant_type' => 'api_only',
    'allow_web_login' => false
]);

echo 'Tenant type: ' . $tenant->fresh()->tenant_type . PHP_EOL;
echo 'Can web login: ' . ($tenant->fresh()->canWebLogin() ? 'Yes' : 'No') . PHP_EOL;
```

### Test User Login Blocking
```php
// Get user from API-only tenant
$user = App\Models\User::where('tenant_id', 2)->first();

// Check if user can login via web
if ($user->tenant && !$user->tenant->canWebLogin()) {
    echo 'LOGIN BLOCKED: This tenant is configured for API-only access' . PHP_EOL;
} else {
    echo 'LOGIN ALLOWED: Tenant can use web login' . PHP_EOL;
}
```

### Create API Credentials
```php
// Create API credentials
$credential = App\Models\TenantApiCredential::create([
    'tenant_id' => $tenant->id,
    'api_key' => 'ak_demo_' . Illuminate\Support\Str::random(32),
    'api_secret' => Illuminate\Support\Str::random(64),
    'allowed_domains' => ['*'],
    'rate_limits' => [
        'requests_per_minute' => 100,
        'requests_per_hour' => 1000
    ],
    'is_active' => true
]);

echo 'API Key: ' . $credential->api_key . PHP_EOL;
```

### Reset Tenant to Regular
```php
// Reset back to regular tenant
$tenant->update([
    'tenant_type' => 'regular',
    'allow_web_login' => true
]);

echo 'Tenant type: ' . $tenant->fresh()->tenant_type . PHP_EOL;
echo 'Can web login: ' . ($tenant->fresh()->canWebLogin() ? 'Yes' : 'No') . PHP_EOL;
```

## Key Features Implemented

### 1. **Tenant Types**
- `regular`: Normal tenant with web login access
- `api_only`: Tenant that can only access via external API

### 2. **Web Login Blocking**
- AuthController blocks login for API-only tenants
- TenantAuthController blocks API login for API-only tenants
- Clear error messages for blocked access

### 3. **API Credentials Management**
- Generate credentials with option to make tenant API-only
- Automatic tenant type conversion
- Rate limiting per tenant
- Usage tracking and monitoring

### 4. **Database Schema**
- `tenant_type` enum field: 'regular' or 'api_only'
- `allow_web_login` boolean field
- Proper relationships between tenants and API credentials

### 5. **Security Features**
- Complete tenant isolation
- API key validation
- Rate limiting
- Request logging
- Token-based authentication

## Use Cases

### Regular Tenant Flow:
1. Admin creates tenant
2. Users can login via web interface
3. Users can also use API endpoints
4. Full access to both web and API

### API-Only Tenant Flow:
1. Admin creates tenant
2. Admin generates API credentials with `make_api_only: true`
3. Tenant is automatically converted to API-only
4. Users cannot login via web interface
5. Users can only access via external API endpoints
6. Perfect for client applications that need data access

## Testing Checklist

- [ ] API-only tenant blocks web login
- [ ] API-only tenant allows external API access
- [ ] Regular tenant allows both web and API access
- [ ] API credentials work correctly
- [ ] Rate limiting functions properly
- [ ] Usage logging is working
- [ ] Error messages are clear and helpful
- [ ] Tenant isolation is maintained

## Next Steps

1. Add admin UI for managing tenant types
2. Implement bulk tenant conversion
3. Add API usage analytics dashboard
4. Create client SDK for easier integration
5. Add webhook support for real-time notifications