# External API Testing Guide

## Overview
This guide provides comprehensive testing for the external API system that allows client applications to authenticate and access tenant-scoped data.

## Prerequisites
1. Database migrations must be run: `php artisan migrate`
2. Application must be running on port 8982 (or configured port)
3. At least one tenant must exist with API credentials generated

## Step 1: Generate API Credentials for a Tenant

### Create API Credentials
```bash
curl -X POST http://localhost:8982/api/v1/tenants/1/api-credentials \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{
    "allowed_domains": ["*"],
    "rate_limits": {
      "requests_per_minute": 100,
      "requests_per_hour": 1000
    }
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
    "tenant_id": 1,
    "credential": {
      "id": 1,
      "tenant_id": 1,
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
        "id": 1,
        "name": "Test Tenant",
        "slug": "test-tenant"
      }
    }
  }
}
```

**Save the API Key and Secret for testing!**

## Step 2: Test External API Authentication

### Login via External API
```bash
curl -X POST http://localhost:8982/api/external/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "X-API-Key: ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-Tenant-ID: 1" \
  -d '{
    "email": "user@example.com",
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
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "tenant_id": 1
    },
    "tenant": {
      "id": 1,
      "name": "Test Tenant",
      "slug": "test-tenant"
    }
  }
}
```

**Save the JWT token for subsequent requests!**

## Step 3: Test Protected Endpoints

### Get Current User
```bash
curl -X GET http://localhost:8982/api/external/v1/auth/user \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-API-Key: ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-Tenant-ID: 1"
```

### Get Tenant Information
```bash
curl -X GET http://localhost:8982/api/external/v1/tenant/info \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-API-Key: ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-Tenant-ID: 1"
```

### Get Tenant Configuration
```bash
curl -X GET http://localhost:8982/api/external/v1/tenant/config \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-API-Key: ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-Tenant-ID: 1"
```

### Get Tenant Users (Tenant-Scoped)
```bash
curl -X GET http://localhost:8982/api/external/v1/users \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-API-Key: ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-Tenant-ID: 1"
```

### Get Specific User
```bash
curl -X GET http://localhost:8982/api/external/v1/users/1 \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-API-Key: ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-Tenant-ID: 1"
```

## Step 4: Test Tenant Isolation

### Try to Access Another Tenant (Should Fail)
```bash
curl -X GET http://localhost:8982/api/external/v1/tenant/info \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-API-Key: ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-Tenant-ID: 2"
```

**Expected Response:**
```json
{
  "success": false,
  "message": "Invalid API credentials"
}
```

### Try to Login with Wrong Tenant ID
```bash
curl -X POST http://localhost:8982/api/external/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "X-API-Key: ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-Tenant-ID: 999" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'
```

**Expected Response:**
```json
{
  "success": false,
  "message": "Invalid API credentials"
}
```

## Step 5: Test Rate Limiting

### Exceed Rate Limit (if configured)
Run the following command rapidly to test rate limiting:
```bash
for i in {1..150}; do
  curl -X GET http://localhost:8982/api/external/v1/auth/user \
    -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
    -H "X-API-Key: ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
    -H "X-Tenant-ID: 1" \
    -w "%{http_code}\n" -s -o /dev/null
done
```

**Expected Response (after limit exceeded):**
```json
{
  "success": false,
  "message": "Rate limit exceeded"
}
```

## Step 6: Test Error Scenarios

### Missing API Key
```bash
curl -X GET http://localhost:8982/api/external/v1/auth/user \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-Tenant-ID: 1"
```

### Missing Tenant ID
```bash
curl -X GET http://localhost:8982/api/external/v1/auth/user \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-API-Key: ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
```

### Invalid Token
```bash
curl -X GET http://localhost:8982/api/external/v1/auth/user \
  -H "Authorization: Bearer invalid_token" \
  -H "X-API-Key: ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-Tenant-ID: 1"
```

## Step 7: Test Token Refresh

### Refresh Token
```bash
curl -X POST http://localhost:8982/api/external/v1/auth/refresh \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-API-Key: ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-Tenant-ID: 1"
```

## Step 8: Test Logout

### Logout
```bash
curl -X POST http://localhost:8982/api/external/v1/auth/logout \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-API-Key: ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "X-Tenant-ID: 1"
```

## Step 9: Check API Usage Logs

### Get API Usage Statistics
```bash
curl -X GET http://localhost:8982/api/v1/tenants/1/api-credentials/1/usage \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

## JavaScript Client Example

Here's a complete JavaScript client example:

```javascript
class OrchestatorApiClient {
  constructor(baseURL, tenantId, apiKey) {
    this.baseURL = baseURL;
    this.tenantId = tenantId;
    this.apiKey = apiKey;
    this.token = null;
  }

  async login(email, password) {
    const response = await fetch(`${this.baseURL}/api/external/v1/auth/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-API-Key': this.apiKey,
        'X-Tenant-ID': this.tenantId
      },
      body: JSON.stringify({ email, password })
    });

    const data = await response.json();
    
    if (data.success) {
      this.token = data.data.token;
      localStorage.setItem('auth_token', this.token);
      return data.data;
    } else {
      throw new Error(data.message);
    }
  }

  async getUser() {
    const response = await fetch(`${this.baseURL}/api/external/v1/auth/user`, {
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'X-API-Key': this.apiKey,
        'X-Tenant-ID': this.tenantId
      }
    });

    const data = await response.json();
    
    if (data.success) {
      return data.data;
    } else {
      throw new Error(data.message);
    }
  }

  async getTenantUsers() {
    const response = await fetch(`${this.baseURL}/api/external/v1/users`, {
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'X-API-Key': this.apiKey,
        'X-Tenant-ID': this.tenantId
      }
    });

    const data = await response.json();
    
    if (data.success) {
      return data.data.users;
    } else {
      throw new Error(data.message);
    }
  }

  async logout() {
    const response = await fetch(`${this.baseURL}/api/external/v1/auth/logout`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'X-API-Key': this.apiKey,
        'X-Tenant-ID': this.tenantId
      }
    });

    if (response.ok) {
      this.token = null;
      localStorage.removeItem('auth_token');
    }
  }
}

// Usage Example
const client = new OrchestatorApiClient(
  'http://localhost:8982',
  '1',
  'ak_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
);

// Login
client.login('user@example.com', 'password123')
  .then(userData => {
    console.log('Login successful:', userData);
    
    // Get current user
    return client.getUser();
  })
  .then(user => {
    console.log('Current user:', user);
    
    // Get tenant users
    return client.getTenantUsers();
  })
  .then(users => {
    console.log('Tenant users:', users);
  })
  .catch(error => {
    console.error('Error:', error.message);
  });
```

## Security Validation Checklist

- [ ] API credentials are required for all external API calls
- [ ] Users can only access data from their own tenant
- [ ] Invalid API keys are rejected
- [ ] Rate limiting works correctly
- [ ] Tokens are properly validated
- [ ] API usage is logged for monitoring
- [ ] Cross-tenant access is prevented
- [ ] Error messages don't expose sensitive information

## Troubleshooting

### Common Issues

1. **Database Connection Error**: Make sure PostgreSQL is running and accessible
2. **Migration Errors**: Run `php artisan migrate` to create the new tables
3. **Authentication Failures**: Verify API key and tenant ID are correct
4. **Rate Limiting Issues**: Check the rate limits configuration in the credentials
5. **Token Expiration**: Tokens expire after 24 hours by default

### Debug Mode

Enable debug mode by setting `APP_DEBUG=true` in your `.env` file for more detailed error messages during development.

## Production Considerations

1. **HTTPS**: Always use HTTPS in production
2. **Rate Limiting**: Adjust rate limits based on your needs
3. **Token Expiration**: Configure appropriate token expiration times
4. **Monitoring**: Set up proper logging and monitoring
5. **Backup**: Regular database backups including API usage logs
6. **Security**: Regular security audits and dependency updates