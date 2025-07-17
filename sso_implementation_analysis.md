# SSO Implementation Analysis & Roadmap

## Current Architecture Assessment

### ✅ **Strong Foundation for SSO**

**1. Multi-Tenant Architecture**
- ✅ Tenant isolation dengan `HasTenant` trait
- ✅ User belongs to specific tenant
- ✅ Role-based access control (RBAC)
- ✅ API authentication dengan Laravel Sanctum

**2. Authentication System**
- ✅ Laravel Sanctum untuk API tokens
- ✅ Session-based authentication untuk web
- ✅ External API dengan tenant validation
- ✅ User model dengan tenant relationship

**3. Security Features**
- ✅ API key/secret per tenant
- ✅ Rate limiting per tenant
- ✅ Domain restrictions
- ✅ Usage logging
- ✅ Encrypted route keys

## SSO Implementation Options

### Option 1: **OAuth 2.0 Provider** (Recommended)
Transform orchestrator menjadi OAuth provider untuk client applications.

#### **Architecture**
```
[Client App 1] ──OAuth──┐
[Client App 2] ──OAuth──┼── [Orchestrator SSO Provider]
[Client App 3] ──OAuth──┘
```

#### **Flow**
1. User klik "Login with Orchestrator" di client app
2. Redirect ke orchestrator OAuth authorization
3. User login sekali di orchestrator
4. Orchestrator return authorization code ke client
5. Client exchange code dengan access token
6. Client use access token untuk API calls

#### **Implementation**
```php
// Install Laravel Passport
composer require laravel/passport

// Create OAuth clients table
php artisan passport:install
php artisan passport:client

// OAuth Controller
class OAuthController extends Controller
{
    public function authorize(Request $request)
    {
        // Validate client_id, redirect_uri, scope
        $client = Client::where('client_id', $request->client_id)->first();
        
        if (!$client || !$client->belongsToTenant($tenantId)) {
            return response()->json(['error' => 'Invalid client'], 400);
        }
        
        // Show authorization page
        return view('oauth.authorize', compact('client'));
    }
    
    public function token(Request $request)
    {
        // Exchange authorization code for access token
        $tokenRequest = Request::create('/oauth/token', 'POST', [
            'grant_type' => 'authorization_code',
            'client_id' => $request->client_id,
            'client_secret' => $request->client_secret,
            'redirect_uri' => $request->redirect_uri,
            'code' => $request->code,
        ]);
        
        return app()->handle($tokenRequest);
    }
}
```

### Option 2: **SAML 2.0 Provider**
Untuk enterprise clients yang butuh SAML SSO.

#### **Implementation**
```php
// Install SAML package
composer require aacotroneo/laravel-saml2

// SAML Configuration per tenant
class SamlController extends Controller
{
    public function sso(Request $request, $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        
        // Configure SAML based on tenant settings
        $samlConfig = $tenant->getSamlConfig();
        
        // Handle SAML request
        $saml = new SamlService($samlConfig);
        return $saml->login();
    }
}
```

### Option 3: **JWT-based SSO**
Lightweight option using JWT tokens.

#### **Implementation**
```php
// JWT SSO Controller
class JWTSSOController extends Controller
{
    public function generateSSOToken(Request $request)
    {
        $user = auth()->user();
        $tenant = $user->tenant;
        
        $payload = [
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions(),
            'exp' => now()->addMinutes(60)->timestamp
        ];
        
        $token = JWT::encode($payload, config('app.key'), 'HS256');
        
        return response()->json([
            'success' => true,
            'sso_token' => $token,
            'expires_in' => 3600
        ]);
    }
    
    public function validateSSOToken(Request $request)
    {
        try {
            $token = $request->bearerToken();
            $payload = JWT::decode($token, config('app.key'), ['HS256']);
            
            $user = User::find($payload->user_id);
            $tenant = Tenant::find($payload->tenant_id);
            
            if (!$user || !$tenant || !$user->belongsToTenant($tenant->id)) {
                return response()->json(['error' => 'Invalid token'], 401);
            }
            
            return response()->json([
                'success' => true,
                'user' => $user,
                'tenant' => $tenant
            ]);
            
        } catch (Exception $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }
}
```

## Enhanced Multi-Tenant SSO Architecture

### **Database Schema Updates**
```sql
-- OAuth clients per tenant
CREATE TABLE oauth_clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    client_id VARCHAR(100) NOT NULL,
    client_secret VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    redirect_uri TEXT NOT NULL,
    scopes JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);

-- SSO sessions
CREATE TABLE sso_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    user_id INT NOT NULL,
    client_id VARCHAR(100) NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- SSO configurations per tenant
ALTER TABLE tenants ADD COLUMN sso_config JSON;
```

### **SSO Service Class**
```php
<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Models\OAuthClient;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SSOService
{
    public function createSSOSession(User $user, string $clientId): array
    {
        $client = OAuthClient::where('client_id', $clientId)
            ->where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->firstOrFail();
        
        $sessionId = Str::random(64);
        
        // Store session in database
        SSOSession::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'client_id' => $clientId,
            'session_id' => $sessionId,
            'expires_at' => now()->addHours(8)
        ]);
        
        return [
            'session_id' => $sessionId,
            'access_token' => $this->generateAccessToken($user, $client),
            'expires_in' => 28800 // 8 hours
        ];
    }
    
    public function validateSSOSession(string $sessionId): ?User
    {
        $session = SSOSession::where('session_id', $sessionId)
            ->where('expires_at', '>', now())
            ->with(['user', 'tenant'])
            ->first();
        
        return $session ? $session->user : null;
    }
    
    public function generateAccessToken(User $user, OAuthClient $client): string
    {
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'aud' => $client->client_id,
            'tenant_id' => $user->tenant_id,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
            'scopes' => $client->scopes ?? [],
            'exp' => now()->addHours(8)->timestamp,
            'iat' => now()->timestamp
        ];
        
        return JWT::encode($payload, config('app.key'), 'HS256');
    }
}
```

## Integration Examples

### **Client Application Integration**

#### **1. OAuth Integration**
```javascript
// Client-side OAuth flow
const ORCHESTRATOR_URL = 'https://orchestrator.example.com';
const CLIENT_ID = 'your-client-id';
const REDIRECT_URI = 'https://your-app.com/callback';

// Step 1: Redirect to orchestrator
function loginWithOrchestrator() {
    const authUrl = `${ORCHESTRATOR_URL}/oauth/authorize?` +
        `client_id=${CLIENT_ID}&` +
        `redirect_uri=${encodeURIComponent(REDIRECT_URI)}&` +
        `response_type=code&` +
        `scope=read write&` +
        `state=${generateRandomState()}`;
    
    window.location.href = authUrl;
}

// Step 2: Handle callback
async function handleCallback(code) {
    const response = await fetch(`${ORCHESTRATOR_URL}/oauth/token`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            grant_type: 'authorization_code',
            client_id: CLIENT_ID,
            client_secret: CLIENT_SECRET,
            redirect_uri: REDIRECT_URI,
            code: code
        })
    });
    
    const { access_token } = await response.json();
    
    // Store token and make API calls
    localStorage.setItem('access_token', access_token);
    
    // Get user info
    const userResponse = await fetch(`${ORCHESTRATOR_URL}/api/user`, {
        headers: {
            'Authorization': `Bearer ${access_token}`
        }
    });
    
    const user = await userResponse.json();
    console.log('Logged in user:', user);
}
```

#### **2. Direct API Integration**
```php
// Client Laravel application
class OrchestratorSSOController extends Controller
{
    public function login(Request $request)
    {
        $orchestratorUrl = config('services.orchestrator.url');
        $clientId = config('services.orchestrator.client_id');
        
        $authUrl = $orchestratorUrl . '/oauth/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => route('orchestrator.callback'),
            'response_type' => 'code',
            'scope' => 'read write',
            'state' => Str::random(40)
        ]);
        
        return redirect($authUrl);
    }
    
    public function callback(Request $request)
    {
        $code = $request->get('code');
        $state = $request->get('state');
        
        // Exchange code for token
        $response = Http::post($orchestratorUrl . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.orchestrator.client_id'),
            'client_secret' => config('services.orchestrator.client_secret'),
            'redirect_uri' => route('orchestrator.callback'),
            'code' => $code
        ]);
        
        $tokenData = $response->json();
        
        // Get user info
        $userResponse = Http::withToken($tokenData['access_token'])
            ->get($orchestratorUrl . '/api/user');
        
        $userData = $userResponse->json();
        
        // Create or update local user
        $user = User::updateOrCreate(
            ['email' => $userData['email']],
            [
                'name' => $userData['name'],
                'orchestrator_id' => $userData['id'],
                'tenant_id' => $userData['tenant_id']
            ]
        );
        
        Auth::login($user);
        
        return redirect()->intended('/dashboard');
    }
}
```

## Implementation Roadmap

### **Phase 1: Foundation (1-2 weeks)**
- [ ] Install Laravel Passport
- [ ] Create OAuth clients table
- [ ] Add SSO configuration to tenants
- [ ] Create basic OAuth controllers

### **Phase 2: Core SSO (2-3 weeks)**
- [ ] Implement OAuth authorization flow
- [ ] Create SSO session management
- [ ] Add JWT token generation
- [ ] Build user validation endpoints

### **Phase 3: Admin Interface (1-2 weeks)**
- [ ] OAuth client management UI
- [ ] SSO configuration per tenant
- [ ] Session monitoring dashboard
- [ ] Usage analytics

### **Phase 4: Documentation & Testing (1 week)**
- [ ] API documentation
- [ ] Integration guides
- [ ] Unit tests
- [ ] End-to-end testing

## Security Considerations

1. **PKCE (Proof Key for Code Exchange)**
   - Implement PKCE for mobile/SPA clients
   - Generate code_verifier and code_challenge

2. **Scope Management**
   - Define granular scopes per tenant
   - Implement scope validation

3. **Token Security**
   - Short-lived access tokens (1-8 hours)
   - Refresh token rotation
   - Token revocation endpoint

4. **Session Management**
   - Single logout (SLO)
   - Session timeout
   - Concurrent session limits

## Benefits of SSO Implementation

✅ **User Experience**: Single login across all applications
✅ **Security**: Centralized authentication and authorization
✅ **Compliance**: Easier audit trails and access control
✅ **Scalability**: Add new applications without separate auth
✅ **Cost Efficiency**: Reduced support and maintenance

## Conclusion

**YES, aplikasi saat ini sangat cocok untuk SSO implementation!** 

Dengan foundation yang sudah ada:
- Multi-tenant architecture ✅
- Laravel Sanctum ✅
- Role-based access control ✅
- API authentication ✅
- Tenant isolation ✅

Anda bisa implement SSO dengan relatif mudah. Saya rekomendasikan mulai dengan **OAuth 2.0** karena:
- Industry standard
- Good Laravel support
- Flexible untuk berbagai client types
- Mudah untuk scaling

Mau saya mulai implementasi SSO step-by-step?