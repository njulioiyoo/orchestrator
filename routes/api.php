<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ExternalAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// External API routes for client applications
Route::prefix('external/v1')->group(function () {
    // Authentication endpoints (no middleware needed)
    Route::post('/auth/login', [ExternalAuthController::class, 'login']);
    
    // Protected endpoints with tenant isolation
    Route::middleware(['auth:sanctum', 'tenant.api.isolation'])->group(function () {
        Route::get('/auth/user', [ExternalAuthController::class, 'user']);
        Route::post('/auth/refresh', [ExternalAuthController::class, 'refresh']);
        Route::post('/auth/logout', [ExternalAuthController::class, 'logout']);
        
        // Tenant info endpoints
        Route::get('/tenant/info', function () {
            $tenantId = app('current_tenant_id');
            $tenant = \App\Models\Tenant::find($tenantId);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'tenant' => [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'slug' => $tenant->slug,
                        'domain' => $tenant->domain,
                        'subdomain' => $tenant->subdomain,
                        'config' => $tenant->config
                    ]
                ]
            ]);
        });
        
        Route::get('/tenant/config', function () {
            $tenantId = app('current_tenant_id');
            $tenant = \App\Models\Tenant::find($tenantId);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'config' => $tenant->config,
                    'features' => $tenant->config['features'] ?? [],
                    'limits' => $tenant->config['limits'] ?? []
                ]
            ]);
        });
        
        // Users endpoints (tenant-scoped)
        Route::get('/users', function () {
            $tenantId = app('current_tenant_id');
            $users = \App\Models\User::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->select('id', 'name', 'email', 'tenant_id', 'created_at')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users
                ]
            ]);
        });
        
        Route::get('/users/{id}', function ($id) {
            $tenantId = app('current_tenant_id');
            $user = \App\Models\User::where('tenant_id', $tenantId)
                ->where('id', $id)
                ->where('is_active', true)
                ->select('id', 'name', 'email', 'tenant_id', 'profile', 'created_at')
                ->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user
                ]
            ]);
        });
    });
});

// Load tenant API routes
require __DIR__.'/api_tenant.php';

// Default API route
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
    ]);
});

// API Documentation endpoint
Route::get('/docs', function () {
    return response()->json([
        'name' => 'Orchestrator API',
        'version' => '1.0.0',
        'description' => 'Multi-tenant management system API',
        'endpoints' => [
            'authentication' => [
                'POST /api/v1/auth/login' => 'Login with tenant context',
                'POST /api/v1/auth/register' => 'Register new user',
                'POST /api/v1/auth/logout' => 'Logout user',
                'GET /api/v1/auth/user' => 'Get current user',
                'POST /api/v1/auth/refresh' => 'Refresh token',
            ],
            'tenants' => [
                'GET /api/v1/tenants' => 'List all tenants (admin only)',
                'POST /api/v1/tenants' => 'Create new tenant',
                'GET /api/v1/tenants/{id}' => 'Get tenant details',
                'PUT /api/v1/tenants/{id}' => 'Update tenant',
                'DELETE /api/v1/tenants/{id}' => 'Delete tenant',
            ],
            'tenant_config' => [
                'GET /api/v1/tenants/{id}/config' => 'Get tenant configuration',
                'PUT /api/v1/tenants/{id}/config' => 'Update tenant configuration',
                'GET /api/v1/tenants/{id}/features' => 'Get tenant features',
                'PUT /api/v1/tenants/{id}/features' => 'Update tenant features',
            ],
            'tenant_users' => [
                'GET /api/v1/tenants/{id}/users' => 'Get tenant users',
                'POST /api/v1/tenants/{id}/users' => 'Create user in tenant',
                'GET /api/v1/tenants/{id}/users/{user_id}' => 'Get user details',
                'PUT /api/v1/tenants/{id}/users/{user_id}' => 'Update user',
                'DELETE /api/v1/tenants/{id}/users/{user_id}' => 'Delete user',
            ],
            'current_tenant' => [
                'GET /api/v1/current-tenant' => 'Get current tenant info',
                'GET /api/v1/current-tenant/config' => 'Get current tenant config',
                'GET /api/v1/current-tenant/users' => 'Get current tenant users',
            ],
        ],
        'authentication' => [
            'type' => 'Bearer Token',
            'header' => 'Authorization: Bearer {token}',
        ],
    ]);
});