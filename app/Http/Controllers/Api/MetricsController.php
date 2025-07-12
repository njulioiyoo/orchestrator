<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Menu;
use OwenIt\Auditing\Models\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MetricsController extends Controller
{
    /**
     * Get performance metrics for dashboard
     *
     * @return JsonResponse
     */
    public function performance(): JsonResponse
    {
        try {
            $metrics = Cache::remember('dashboard_performance_metrics', 300, function () {
                return [
                    'user_metrics' => $this->getUserMetrics(),
                    'system_metrics' => $this->getSystemMetrics(),
                    'activity_metrics' => $this->getActivityMetrics(),
                    'database_metrics' => $this->getDatabaseMetrics(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $metrics,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch performance metrics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system health status
     *
     * @return JsonResponse
     */
    public function health(): JsonResponse
    {
        try {
            $health = [
                'status' => 'healthy',
                'checks' => [
                    'database' => $this->checkDatabase(),
                    'cache' => $this->checkCache(),
                    'storage' => $this->checkStorage(),
                    'memory' => $this->checkMemory(),
                ],
                'timestamp' => now()->toISOString()
            ];

            // Determine overall status
            $failedChecks = collect($health['checks'])->where('status', 'unhealthy')->count();
            if ($failedChecks > 0) {
                $health['status'] = $failedChecks > 2 ? 'critical' : 'warning';
            }

            return response()->json([
                'success' => true,
                'data' => $health
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [
                    'status' => 'critical',
                    'error' => $e->getMessage(),
                    'timestamp' => now()->toISOString()
                ]
            ], 500);
        }
    }

    /**
     * Get user-related metrics
     *
     * @return array
     */
    private function getUserMetrics(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::whereNotNull('email_verified_at')->count();
        $recentUsers = User::where('created_at', '>=', now()->subDays(7))->count();
        $usersByRole = Role::withCount('users')->get()->pluck('users_count', 'name');

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'recent_users' => $recentUsers,
            'activation_rate' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 2) : 0,
            'users_by_role' => $usersByRole,
            'growth_rate' => $this->calculateUserGrowthRate()
        ];
    }

    /**
     * Get system-related metrics
     *
     * @return array
     */
    private function getSystemMetrics(): array
    {
        return [
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'total_menus' => Menu::count(),
            'active_menus' => Menu::where('is_active', true)->count(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'uptime' => $this->getSystemUptime()
        ];
    }

    /**
     * Get activity-related metrics
     *
     * @return array
     */
    private function getActivityMetrics(): array
    {
        $totalAudits = Audit::count();
        $recentAudits = Audit::where('created_at', '>=', now()->subDays(7))->count();
        $auditsByEvent = Audit::select('event', DB::raw('count(*) as count'))
            ->groupBy('event')
            ->pluck('count', 'event');

        return [
            'total_activities' => $totalAudits,
            'recent_activities' => $recentAudits,
            'activities_by_type' => $auditsByEvent,
            'daily_activity' => $this->getDailyActivityCount()
        ];
    }

    /**
     * Get database-related metrics
     *
     * @return array
     */
    private function getDatabaseMetrics(): array
    {
        $tables = [
            'users' => User::count(),
            'roles' => Role::count(),
            'permissions' => Permission::count(),
            'menus' => Menu::count(),
            'audits' => Audit::count()
        ];

        return [
            'table_counts' => $tables,
            'total_records' => array_sum($tables),
            'database_size' => $this->getDatabaseSize()
        ];
    }

    /**
     * Check database connectivity
     *
     * @return array
     */
    private function checkDatabase(): array
    {
        try {
            DB::select('SELECT 1');
            $responseTime = $this->measureDatabaseResponseTime();
            
            return [
                'status' => $responseTime < 100 ? 'healthy' : 'warning',
                'response_time' => $responseTime,
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check cache functionality
     *
     * @return array
     */
    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 60);
            $value = Cache::get($testKey);
            Cache::forget($testKey);

            return [
                'status' => $value === 'test' ? 'healthy' : 'unhealthy',
                'message' => $value === 'test' ? 'Cache working properly' : 'Cache test failed'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Cache check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check storage accessibility
     *
     * @return array
     */
    private function checkStorage(): array
    {
        try {
            $storagePath = storage_path();
            $isWritable = is_writable($storagePath);
            $freeSpace = disk_free_space($storagePath);
            $totalSpace = disk_total_space($storagePath);
            $usagePercent = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);

            return [
                'status' => $isWritable && $usagePercent < 90 ? 'healthy' : 'warning',
                'writable' => $isWritable,
                'free_space' => $this->formatBytes($freeSpace),
                'total_space' => $this->formatBytes($totalSpace),
                'usage_percent' => $usagePercent,
                'message' => $isWritable ? 'Storage accessible' : 'Storage not writable'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Storage check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check memory usage
     *
     * @return array
     */
    private function checkMemory(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = $this->getMemoryLimit();
        $usagePercent = $memoryLimit > 0 ? round(($memoryUsage / $memoryLimit) * 100, 2) : 0;

        return [
            'status' => $usagePercent < 80 ? 'healthy' : 'warning',
            'current_usage' => $this->formatBytes($memoryUsage),
            'peak_usage' => $this->formatBytes($memoryPeak),
            'limit' => $memoryLimit > 0 ? $this->formatBytes($memoryLimit) : 'Unlimited',
            'usage_percent' => $usagePercent,
            'message' => 'Memory usage within limits'
        ];
    }

    /**
     * Calculate user growth rate
     *
     * @return float
     */
    private function calculateUserGrowthRate(): float
    {
        $currentWeekUsers = User::where('created_at', '>=', now()->subDays(7))->count();
        $previousWeekUsers = User::whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count();

        if ($previousWeekUsers == 0) {
            return $currentWeekUsers > 0 ? 100 : 0;
        }

        return round((($currentWeekUsers - $previousWeekUsers) / $previousWeekUsers) * 100, 2);
    }

    /**
     * Get system uptime
     *
     * @return string
     */
    private function getSystemUptime(): string
    {
        if (function_exists('sys_getloadavg')) {
            $uptime = shell_exec('uptime');
            return $uptime ? trim($uptime) : 'Unknown';
        }
        return 'Not available';
    }

    /**
     * Get daily activity count for the last 7 days
     *
     * @return array
     */
    private function getDailyActivityCount(): array
    {
        return Audit::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();
    }

    /**
     * Get database size
     *
     * @return string
     */
    private function getDatabaseSize(): string
    {
        try {
            $result = DB::select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'DB Size in MB'
                FROM information_schema.tables 
                WHERE table_schema = ?
            ", [config('database.connections.mysql.database')]);

            return isset($result[0]) ? $result[0]->{'DB Size in MB'} . ' MB' : 'Unknown';
        } catch (\Exception $e) {
            return 'Unable to calculate';
        }
    }

    /**
     * Measure database response time
     *
     * @return float
     */
    private function measureDatabaseResponseTime(): float
    {
        $start = microtime(true);
        DB::select('SELECT 1');
        $end = microtime(true);
        
        return round(($end - $start) * 1000, 2); // Return in milliseconds
    }

    /**
     * Get memory limit in bytes
     *
     * @return int
     */
    private function getMemoryLimit(): int
    {
        $limit = ini_get('memory_limit');
        if ($limit == -1) {
            return 0; // Unlimited
        }
        
        return $this->convertToBytes($limit);
    }

    /**
     * Convert memory limit string to bytes
     *
     * @param string $value
     * @return int
     */
    private function convertToBytes(string $value): int
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $number = (int) $value;
        
        switch ($last) {
            case 'g':
                $number *= 1024;
            case 'm':
                $number *= 1024;
            case 'k':
                $number *= 1024;
        }
        
        return $number;
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}