# Tenant API Testing with Real Data

## ✅ API Controllers Created
All API controllers have been successfully created:
- **TenantApiController** - Main tenant management
- **TenantAuthController** - Authentication with tenant context
- **TenantConfigController** - Tenant configuration management
- **TenantUserController** - User management per tenant

## ✅ Middleware Created
- **TenantAccessMiddleware** - Ensures user has access to specific tenant
- **TenantResolveMiddleware** - Resolves tenant context from authenticated user

## ✅ Routes Registered
- API routes are registered in `routes/api.php`
- All endpoints are available under `/api/v1/`

# Tenant API Testing with Real Data

## Data yang Sudah Dibuat

### Tenants
1. **Masjid Al-Ikhlas** (ID: 1)
   - Slug: `masjid-al-ikhlas`
   - Business Type: `mosque`
   - Domain: `masjid-al-ikhlas.com`
   - Subdomain: `masjid`

2. **Rental Sukses Mandiri** (ID: 2)
   - Slug: `rental-sukses-mandiri`
   - Business Type: `rental`
   - Domain: `rentalsukses.com`
   - Subdomain: `rental`

### Users
1. **Ahmad Imam** (ID: 1, Tenant: 1)
   - Email: `imam@masjid-al-ikhlas.com`
   - Password: `password123`
   - Role: Admin Masjid

2. **Sari Rental** (ID: 2, Tenant: 2)
   - Email: `owner@rentalsukses.com`
   - Password: `password123`
   - Role: Owner Rental

3. **Budi Staff** (ID: 3, Tenant: 2)
   - Email: `staff@rentalsukses.com`
   - Password: `password123`
   - Role: Staff Rental

## API Testing Commands

### 1. Test Tenant Resolution
```bash
# Test masjid tenant info
curl -X GET http://localhost:8982/api/v1/tenants/masjid-al-ikhlas/info

# Test rental tenant info
curl -X GET http://localhost:8982/api/v1/tenants/rental-sukses-mandiri/info
```

### 2. Test Authentication

#### Login Imam Masjid
```bash
curl -X POST http://localhost:8982/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "imam@masjid-al-ikhlas.com",
    "password": "password123",
    "tenant_slug": "masjid-al-ikhlas"
  }'
```

#### Login Rental Owner
```bash
curl -X POST http://localhost:8982/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "owner@rentalsukses.com",
    "password": "password123",
    "tenant_slug": "rental-sukses-mandiri"
  }'
```

#### Login Rental Staff
```bash
curl -X POST http://localhost:8982/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "staff@rentalsukses.com",
    "password": "password123",
    "tenant_slug": "rental-sukses-mandiri"
  }'
```

### 3. Test Tenant Configuration (After Login)

#### Get Masjid Configuration
```bash
curl -X GET http://localhost:8982/api/v1/current-tenant/config \
  -H "Authorization: Bearer {TOKEN_FROM_LOGIN}"
```

#### Get Rental Configuration
```bash
curl -X GET http://localhost:8982/api/v1/current-tenant/config \
  -H "Authorization: Bearer {TOKEN_FROM_LOGIN}"
```

### 4. Test Tenant Users

#### Get Masjid Users
```bash
curl -X GET http://localhost:8982/api/v1/current-tenant/users \
  -H "Authorization: Bearer {IMAM_TOKEN}"
```

#### Get Rental Users
```bash
curl -X GET http://localhost:8982/api/v1/current-tenant/users \
  -H "Authorization: Bearer {OWNER_TOKEN}"
```

### 5. Test Feature Management

#### Update Masjid Features
```bash
curl -X PUT http://localhost:8982/api/v1/current-tenant/features \
  -H "Authorization: Bearer {IMAM_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "features": {
      "donation_management": true,
      "event_management": true,
      "prayer_schedule": true,
      "inventory_management": true,
      "library_management": true,
      "financial_reports": true
    }
  }'
```

#### Update Rental Features
```bash
curl -X PUT http://localhost:8982/api/v1/current-tenant/features \
  -H "Authorization: Bearer {OWNER_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "features": {
      "rental_management": true,
      "inventory_management": true,
      "booking_system": true,
      "customer_management": true,
      "payment_tracking": true,
      "invoice_generation": true,
      "maintenance_tracking": true
    }
  }'
```

## Expected Responses

### Masjid Login Response
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Ahmad Imam",
      "email": "imam@masjid-al-ikhlas.com",
      "tenant_id": 1,
      "profile": {
        "position": "Ketua Pengurus Masjid",
        "phone": "081234567890"
      }
    },
    "tenant": {
      "id": 1,
      "name": "Masjid Al-Ikhlas",
      "business_type": "mosque",
      "branding": {
        "app_name": "Masjid Al-Ikhlas Management",
        "primary_color": "#2D5A27"
      }
    },
    "token": "...",
    "expires_at": "..."
  }
}
```

### Rental Login Response
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 2,
      "name": "Sari Rental",
      "email": "owner@rentalsukses.com",
      "tenant_id": 2,
      "profile": {
        "position": "Pemilik Usaha",
        "phone": "081987654321"
      }
    },
    "tenant": {
      "id": 2,
      "name": "Rental Sukses Mandiri",
      "business_type": "rental",
      "branding": {
        "app_name": "Rental Management System",
        "primary_color": "#1E40AF"
      }
    },
    "token": "...",
    "expires_at": "..."
  }
}
```

## Database Verification Commands

### Check Tenant Data
```bash
docker exec orchestrator php artisan tinker --execute="
App\Models\Tenant::find(1)->makeVisible(['config'])->toJson();
"
```

### Check User Data
```bash
docker exec orchestrator php artisan tinker --execute="
App\Models\User::with('tenant')->find(1)->makeVisible(['profile', 'permissions', 'settings'])->toJson();
"
```

### Check Tenant Features
```bash
docker exec orchestrator php artisan tinker --execute="
\$tenant = App\Models\Tenant::find(1);
echo json_encode(\$tenant->config['features'] ?? []);
"
```

## Testing Scenarios

### Scenario 1: Imam Masjid Daily Operations
1. Login as imam
2. Check masjid configuration
3. View available features (donation, event, prayer schedule)
4. Update prayer schedule feature
5. Check user permissions

### Scenario 2: Rental Owner Management
1. Login as owner
2. Check rental configuration
3. View available features (inventory, booking, payment)
4. Add new staff user
5. Update inventory limits

### Scenario 3: Staff Operations
1. Login as staff
2. Check limited permissions
3. View inventory only
4. Cannot modify configuration

## Performance Testing

### Load Test Login
```bash
# Test 100 concurrent logins
for i in {1..100}; do
  curl -X POST http://localhost:8982/api/v1/auth/login \
    -H "Content-Type: application/json" \
    -d '{
      "email": "imam@masjid-al-ikhlas.com",
      "password": "password123",
      "tenant_slug": "masjid-al-ikhlas"
    }' &
done
wait
```

### Test Tenant Isolation
```bash
# Verify tenant 1 user cannot access tenant 2 data
# Login with tenant 1 token, try to access tenant 2 endpoint
curl -X GET http://localhost:8982/api/v1/tenants/2/config \
  -H "Authorization: Bearer {TENANT_1_TOKEN}"
# Should return 403 Forbidden
```