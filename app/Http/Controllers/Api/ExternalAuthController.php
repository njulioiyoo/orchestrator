<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TenantApiCredential;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ExternalAuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $apiKey = $request->header('X-API-Key');
        $tenantId = $request->header('X-Tenant-ID');

        if (!$apiKey || !$tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'Missing API credentials'
            ], 401);
        }

        $credentials = TenantApiCredential::where('api_key', $apiKey)
            ->where('tenant_id', $tenantId)
            ->active()
            ->first();

        if (!$credentials) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API credentials'
            ], 401);
        }

        $user = User::where('email', $request->email)
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('external-api-' . $tenantId, ['external-api'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'tenant_id' => $user->tenant_id
                ],
                'tenant' => [
                    'id' => $user->tenant->id,
                    'name' => $user->tenant->name,
                    'slug' => $user->tenant->slug
                ]
            ]
        ]);
    }

    public function user(Request $request)
    {
        $user = Auth::user();
        $tenantId = app('current_tenant_id');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'tenant_id' => $user->tenant_id
                ],
                'tenant' => [
                    'id' => $user->tenant->id,
                    'name' => $user->tenant->name,
                    'slug' => $user->tenant->slug
                ],
                'permissions' => $user->getAllPermissions()->pluck('name')
            ]
        ]);
    }

    public function refresh(Request $request)
    {
        $user = Auth::user();
        $tenantId = app('current_tenant_id');

        $user->tokens()->where('name', 'external-api-' . $tenantId)->delete();

        $token = $user->createToken('external-api-' . $tenantId, ['external-api'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $tenantId = app('current_tenant_id');

        $user->tokens()->where('name', 'external-api-' . $tenantId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }
}
