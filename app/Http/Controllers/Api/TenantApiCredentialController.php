<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantApiCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TenantApiCredentialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Tenant $tenant)
    {
        $credentials = TenantApiCredential::where('tenant_id', $tenant->id)
            ->with('tenant:id,name,slug')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'credentials' => $credentials
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Tenant $tenant)
    {
        \Log::info('API Credentials Store Request', [
            'user' => auth()->user() ? auth()->user()->id : 'not authenticated',
            'tenant' => $tenant->id,
            'request_data' => $request->all()
        ]);
        
        $validator = Validator::make($request->all(), [
            'allowed_domains' => 'nullable|array',
            'allowed_domains.*' => 'string',
            'rate_limits' => 'nullable|array',
            'rate_limits.requests_per_minute' => 'nullable|integer|min:1|max:1000',
            'rate_limits.requests_per_hour' => 'nullable|integer|min:1|max:10000',
            'expires_at' => 'nullable|date|after:now',
            'make_api_only' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $apiKey = 'ak_' . Str::random(32);
        $apiSecret = Str::random(64);

        $credential = TenantApiCredential::create([
            'tenant_id' => $tenant->id,
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'allowed_domains' => $request->allowed_domains ?? ['*'],
            'rate_limits' => $request->rate_limits ?? [
                'requests_per_minute' => 100,
                'requests_per_hour' => 1000
            ],
            'expires_at' => $request->expires_at,
            'is_active' => true
        ]);

        // If requested, make this tenant API-only
        if ($request->boolean('make_api_only')) {
            $tenant->update([
                'tenant_type' => 'api_only',
                'allow_web_login' => false
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'API credentials generated successfully',
            'data' => [
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
                'tenant_id' => $tenant->id,
                'credential' => $credential->load('tenant:id,name,slug')
            ]
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant, TenantApiCredential $credential)
    {
        if ($credential->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Credential not found for this tenant'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'credential' => $credential->load('tenant:id,name,slug')
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tenant $tenant, TenantApiCredential $credential)
    {
        if ($credential->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Credential not found for this tenant'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'allowed_domains' => 'nullable|array',
            'allowed_domains.*' => 'string',
            'rate_limits' => 'nullable|array',
            'rate_limits.requests_per_minute' => 'nullable|integer|min:1|max:1000',
            'rate_limits.requests_per_hour' => 'nullable|integer|min:1|max:10000',
            'is_active' => 'boolean',
            'expires_at' => 'nullable|date|after:now'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $credential->update($request->only([
            'allowed_domains', 'rate_limits', 'is_active', 'expires_at'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'API credentials updated successfully',
            'data' => [
                'credential' => $credential->load('tenant:id,name,slug')
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant, TenantApiCredential $credential)
    {
        if ($credential->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Credential not found for this tenant'
            ], 404);
        }

        $credential->delete();

        return response()->json([
            'success' => true,
            'message' => 'API credentials deleted successfully'
        ]);
    }

    /**
     * Regenerate API secret for existing credential
     */
    public function regenerateSecret(Tenant $tenant, TenantApiCredential $credential)
    {
        if ($credential->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Credential not found for this tenant'
            ], 404);
        }

        $newSecret = Str::random(64);
        $credential->update(['api_secret' => $newSecret]);

        return response()->json([
            'success' => true,
            'message' => 'API secret regenerated successfully',
            'data' => [
                'api_key' => $credential->api_key,
                'api_secret' => $newSecret,
                'tenant_id' => $tenant->id
            ]
        ]);
    }

    /**
     * Get API usage statistics for a credential
     */
    public function usage(Tenant $tenant, TenantApiCredential $credential)
    {
        if ($credential->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Credential not found for this tenant'
            ], 404);
        }

        $usage = \App\Models\ApiUsageLog::where('tenant_id', $tenant->id)
            ->where('api_key', $credential->api_key)
            ->selectRaw('
                COUNT(*) as total_requests,
                COUNT(CASE WHEN response_status >= 200 AND response_status < 300 THEN 1 END) as successful_requests,
                COUNT(CASE WHEN response_status >= 400 THEN 1 END) as failed_requests,
                AVG(response_time_ms) as avg_response_time,
                MAX(created_at) as last_request_at
            ')
            ->first();

        $todayUsage = \App\Models\ApiUsageLog::where('tenant_id', $tenant->id)
            ->where('api_key', $credential->api_key)
            ->whereDate('created_at', today())
            ->count();

        $hourlyUsage = \App\Models\ApiUsageLog::where('tenant_id', $tenant->id)
            ->where('api_key', $credential->api_key)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'usage' => [
                    'total_requests' => $usage->total_requests ?? 0,
                    'successful_requests' => $usage->successful_requests ?? 0,
                    'failed_requests' => $usage->failed_requests ?? 0,
                    'avg_response_time' => round($usage->avg_response_time ?? 0, 2),
                    'last_request_at' => $usage->last_request_at,
                    'today_requests' => $todayUsage,
                    'last_hour_requests' => $hourlyUsage
                ]
            ]
        ]);
    }
}