<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class TenantAuthController extends Controller
{
    protected TenantContext $tenantContext;

    public function __construct(TenantContext $tenantContext)
    {
        $this->tenantContext = $tenantContext;
    }

    /**
     * Login user with tenant context
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'tenant_slug' => 'required|string|exists:tenants,slug',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find tenant
        $tenant = Tenant::where('slug', $request->tenant_slug)
            ->where('is_active', true)
            ->first();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found or inactive'
            ], 404);
        }

        // Check if tenant is expired
        if ($tenant->expires_at && $tenant->expires_at < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant subscription has expired'
            ], 403);
        }

        // Find user in tenant
        $user = User::where('email', $request->email)
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Create token
        $token = $user->createToken('auth_token', ['*'], now()->addHours(24))->plainTextToken;

        // Set tenant context in session
        session(['tenant_id' => $tenant->id]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'tenant_id' => $user->tenant_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                    'profile' => $user->profile,
                    'permissions' => $user->permissions,
                    'settings' => $user->settings,
                    'last_login' => $user->last_login_at,
                ],
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'business_type' => $tenant->business_type,
                    'domain' => $tenant->domain,
                    'subdomain' => $tenant->subdomain,
                    'branding' => $tenant->config['branding'] ?? [],
                    'available_features' => array_keys(array_filter($tenant->config['features'] ?? [])),
                    'expires_at' => $tenant->expires_at,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => now()->addHours(24)->toISOString(),
            ]
        ]);
    }

    /**
     * Register a new user (if tenant allows registration)
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            'tenant_slug' => 'required|string|exists:tenants,slug',
            'profile' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find tenant
        $tenant = Tenant::where('slug', $request->tenant_slug)
            ->where('is_active', true)
            ->first();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found or inactive'
            ], 404);
        }

        // Check if registration is allowed
        if (!($tenant->config['allow_registration'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'Registration is not allowed for this tenant'
            ], 403);
        }

        // Check if user already exists
        $existingUser = User::where('email', $request->email)
            ->where('tenant_id', $tenant->id)
            ->first();

        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'User already exists'
            ], 409);
        }

        // Check user limit
        $userCount = User::where('tenant_id', $tenant->id)->count();
        $maxUsers = $tenant->config['max_users'] ?? 10;

        if ($userCount >= $maxUsers) {
            return response()->json([
                'success' => false,
                'message' => 'User limit reached for this tenant'
            ], 403);
        }

        // Create user
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
            'profile' => $request->profile ?? [],
            'permissions' => [],
            'settings' => [
                'notification_email' => true,
                'dashboard_theme' => 'light',
                'language' => $tenant->locale ?? 'id',
                'timezone' => $tenant->timezone ?? 'Asia/Jakarta',
            ],
            'email_verified_at' => ($tenant->config['email_verification'] ?? true) ? null : now(),
        ]);

        // Create token
        $token = $user->createToken('auth_token', ['*'], now()->addHours(24))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'tenant_id' => $user->tenant_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                    'profile' => $user->profile,
                    'email_verified' => $user->email_verified_at !== null,
                ],
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'business_type' => $tenant->business_type,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => now()->addHours(24)->toISOString(),
            ]
        ], 201);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        
        // Clear tenant context
        session()->forget('tenant_id');

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'tenant_id' => $user->tenant_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                    'profile' => $user->profile,
                    'permissions' => $user->permissions,
                    'settings' => $user->settings,
                    'last_login' => $user->last_login_at,
                    'email_verified' => $user->email_verified_at !== null,
                ],
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'business_type' => $tenant->business_type,
                    'branding' => $tenant->config['branding'] ?? [],
                    'available_features' => array_keys(array_filter($tenant->config['features'] ?? [])),
                ]
            ]
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Delete current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken('auth_token', ['*'], now()->addHours(24))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => now()->addHours(24)->toISOString(),
            ]
        ]);
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'tenant_slug' => 'required|string|exists:tenants,slug',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find tenant
        $tenant = Tenant::where('slug', $request->tenant_slug)
            ->where('is_active', true)
            ->first();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found or inactive'
            ], 404);
        }

        // Find user in tenant
        $user = User::where('email', $request->email)
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Send password reset email
        $status = Password::sendResetLink([
            'email' => $request->email,
            'tenant_id' => $tenant->id,
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send password reset link'
        ], 500);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            'tenant_slug' => 'required|string|exists:tenants,slug',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find tenant
        $tenant = Tenant::where('slug', $request->tenant_slug)
            ->where('is_active', true)
            ->first();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found or inactive'
            ], 404);
        }

        // Reset password
        $status = Password::reset([
            'email' => $request->email,
            'password' => $request->password,
            'password_confirmation' => $request->password_confirmation,
            'token' => $request->token,
            'tenant_id' => $tenant->id,
        ], function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
        });

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to reset password',
            'error' => __($status)
        ], 400);
    }
}