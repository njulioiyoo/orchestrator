<?php

namespace App\Http\Middleware;

use App\Models\ApiUsageLog;
use App\Models\TenantApiCredential;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TenantApiIsolation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
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

        if (!$this->checkRateLimit($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded'
            ], 429);
        }

        app()->instance('current_tenant_id', (int)$tenantId);
        app()->instance('current_api_key', $apiKey);

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->tenant_id !== (int)$tenantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authorized for this tenant'
                ], 403);
            }
        }

        $response = $next($request);

        $this->logApiUsage($request, $response, $startTime);

        return $response;
    }

    private function checkRateLimit(TenantApiCredential $credentials): bool
    {
        if (!$credentials->rate_limits) {
            return true;
        }

        $rateLimits = $credentials->rate_limits;
        $tenantId = $credentials->tenant_id;
        $currentMinute = now()->format('Y-m-d H:i');
        $currentHour = now()->format('Y-m-d H');

        if (isset($rateLimits['requests_per_minute'])) {
            $minuteKey = "api_rate_limit_minute:{$tenantId}:{$currentMinute}";
            $currentMinuteCount = Cache::get($minuteKey, 0);
            
            if ($currentMinuteCount >= $rateLimits['requests_per_minute']) {
                return false;
            }
            
            Cache::put($minuteKey, $currentMinuteCount + 1, 60);
        }

        if (isset($rateLimits['requests_per_hour'])) {
            $hourKey = "api_rate_limit_hour:{$tenantId}:{$currentHour}";
            $currentHourCount = Cache::get($hourKey, 0);
            
            if ($currentHourCount >= $rateLimits['requests_per_hour']) {
                return false;
            }
            
            Cache::put($hourKey, $currentHourCount + 1, 3600);
        }

        return true;
    }

    private function logApiUsage(Request $request, Response $response, float $startTime): void
    {
        $tenantId = app('current_tenant_id');
        $apiKey = app('current_api_key');
        $responseTime = round((microtime(true) - $startTime) * 1000);

        try {
            ApiUsageLog::create([
                'tenant_id' => $tenantId,
                'api_key' => $apiKey,
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'response_status' => $response->getStatusCode(),
                'response_time_ms' => $responseTime,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log API usage: ' . $e->getMessage());
        }
    }
}
