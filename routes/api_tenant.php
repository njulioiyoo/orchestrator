<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TenantApiController;
use App\Http\Controllers\Api\TenantAuthController;
use App\Http\Controllers\Api\TenantUserController;
use App\Http\Controllers\Api\TenantConfigController;

/*
|--------------------------------------------------------------------------
| API Routes for Tenant Management
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for tenant management.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    
    // Tenant resolution routes
    Route::get('/tenants/resolve', [TenantApiController::class, 'resolveTenant']);
    Route::get('/tenants/{slug}/info', [TenantApiController::class, 'getTenantInfo']);
    
    // Authentication routes
    Route::post('/auth/login', [TenantAuthController::class, 'login']);
    Route::post('/auth/register', [TenantAuthController::class, 'register']);
    Route::post('/auth/forgot-password', [TenantAuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [TenantAuthController::class, 'resetPassword']);
});

// Protected routes (requires authentication)
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    
    // Authentication routes
    Route::post('/auth/logout', [TenantAuthController::class, 'logout']);
    Route::get('/auth/user', [TenantAuthController::class, 'user']);
    Route::post('/auth/refresh', [TenantAuthController::class, 'refresh']);
    
    // Tenant management routes (admin only)
    Route::middleware(['permission:manage_tenants'])->group(function () {
        Route::apiResource('tenants', TenantApiController::class);
        Route::post('/tenants/{tenant}/switch', [TenantApiController::class, 'switchTenant']);
        Route::get('/tenants/{tenant}/stats', [TenantApiController::class, 'getTenantStats']);
        Route::put('/tenants/{tenant}/status', [TenantApiController::class, 'updateStatus']);
    });
    
    // Tenant configuration routes
    Route::prefix('tenants/{tenant}')->middleware(['tenant.access'])->group(function () {
        Route::get('/config', [TenantConfigController::class, 'getConfig']);
        Route::put('/config', [TenantConfigController::class, 'updateConfig']);
        Route::get('/features', [TenantConfigController::class, 'getFeatures']);
        Route::put('/features', [TenantConfigController::class, 'updateFeatures']);
        Route::get('/branding', [TenantConfigController::class, 'getBranding']);
        Route::put('/branding', [TenantConfigController::class, 'updateBranding']);
        Route::get('/limits', [TenantConfigController::class, 'getLimits']);
        Route::put('/limits', [TenantConfigController::class, 'updateLimits']);
    });
    
    // Tenant user management routes
    Route::prefix('tenants/{tenant}/users')->middleware(['tenant.access'])->group(function () {
        Route::get('/', [TenantUserController::class, 'index']);
        Route::post('/', [TenantUserController::class, 'store']);
        Route::get('/{user}', [TenantUserController::class, 'show']);
        Route::put('/{user}', [TenantUserController::class, 'update']);
        Route::delete('/{user}', [TenantUserController::class, 'destroy']);
        Route::put('/{user}/status', [TenantUserController::class, 'updateStatus']);
        Route::post('/{user}/permissions', [TenantUserController::class, 'updatePermissions']);
    });
    
    // Current tenant context routes
    Route::prefix('current-tenant')->middleware(['tenant.resolve'])->group(function () {
        Route::get('/', [TenantApiController::class, 'getCurrentTenant']);
        Route::get('/config', [TenantConfigController::class, 'getCurrentTenantConfig']);
        Route::get('/features', [TenantConfigController::class, 'getCurrentTenantFeatures']);
        Route::get('/users', [TenantUserController::class, 'getCurrentTenantUsers']);
        Route::get('/stats', [TenantApiController::class, 'getCurrentTenantStats']);
    });
});