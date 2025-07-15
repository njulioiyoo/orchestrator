<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TenantConfigController extends Controller
{
    protected TenantContext $tenantContext;

    public function __construct(TenantContext $tenantContext)
    {
        $this->tenantContext = $tenantContext;
    }

    /**
     * Get tenant configuration
     */
    public function getConfig(Tenant $tenant): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'business_type' => $tenant->business_type,
                    'phone' => $tenant->phone,
                    'address' => $tenant->address,
                    'primary_color' => $tenant->primary_color,
                    'secondary_color' => $tenant->secondary_color,
                    'timezone' => $tenant->timezone,
                    'locale' => $tenant->locale,
                    'currency' => $tenant->currency,
                ],
                'config' => $tenant->config ?? [],
            ]
        ]);
    }

    /**
     * Update tenant configuration
     */
    public function updateConfig(Request $request, Tenant $tenant): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'config' => 'required|array',
            'config.allow_registration' => 'boolean',
            'config.email_verification' => 'boolean',
            'config.two_factor_auth' => 'boolean',
            'config.max_users' => 'integer|min:1|max:1000',
            'config.max_storage_mb' => 'integer|min:100|max:10240',
            'config.features' => 'array',
            'config.branding' => 'array',
            'config.notification_settings' => 'array',
            'config.limits' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $currentConfig = $tenant->config ?? [];
        $newConfig = array_merge($currentConfig, $request->config);

        $tenant->update(['config' => $newConfig]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant configuration updated successfully',
            'data' => [
                'tenant_id' => $tenant->id,
                'updated_config' => $newConfig,
                'updated_at' => $tenant->fresh()->updated_at,
            ]
        ]);
    }

    /**
     * Get tenant features
     */
    public function getFeatures(Tenant $tenant): JsonResponse
    {
        $features = $tenant->config['features'] ?? [];
        
        return response()->json([
            'success' => true,
            'data' => [
                'tenant_id' => $tenant->id,
                'features' => $features,
                'enabled_features' => array_keys(array_filter($features)),
                'disabled_features' => array_keys(array_filter($features, fn($enabled) => !$enabled)),
                'total_features' => count($features),
                'enabled_count' => count(array_filter($features)),
            ]
        ]);
    }

    /**
     * Update tenant features
     */
    public function updateFeatures(Request $request, Tenant $tenant): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'features' => 'required|array',
            'features.*' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $currentConfig = $tenant->config ?? [];
        $oldFeatures = $currentConfig['features'] ?? [];
        $newFeatures = $request->features;

        // Track changes
        $changes = [];
        foreach ($newFeatures as $feature => $enabled) {
            $oldValue = $oldFeatures[$feature] ?? false;
            if ($oldValue !== $enabled) {
                $changes[] = [
                    'feature' => $feature,
                    'old_value' => $oldValue,
                    'new_value' => $enabled,
                    'changed_at' => now()->toISOString(),
                ];
            }
        }

        // Update features
        $currentConfig['features'] = $newFeatures;
        $tenant->update(['config' => $currentConfig]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant features updated successfully',
            'data' => [
                'tenant_id' => $tenant->id,
                'updated_features' => $newFeatures,
                'changes' => $changes,
                'enabled_features' => array_keys(array_filter($newFeatures)),
                'updated_at' => $tenant->fresh()->updated_at,
            ]
        ]);
    }

    /**
     * Get tenant branding
     */
    public function getBranding(Tenant $tenant): JsonResponse
    {
        $branding = $tenant->config['branding'] ?? [];
        
        return response()->json([
            'success' => true,
            'data' => [
                'tenant_id' => $tenant->id,
                'branding' => array_merge([
                    'app_name' => $tenant->name,
                    'primary_color' => $tenant->primary_color,
                    'secondary_color' => $tenant->secondary_color,
                    'logo_path' => $tenant->logo_path,
                ], $branding),
            ]
        ]);
    }

    /**
     * Update tenant branding
     */
    public function updateBranding(Request $request, Tenant $tenant): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'branding' => 'required|array',
            'branding.app_name' => 'nullable|string|max:255',
            'branding.tagline' => 'nullable|string|max:255',
            'branding.logo_path' => 'nullable|string|max:255',
            'branding.favicon' => 'nullable|string|max:255',
            'branding.custom_css' => 'nullable|string',
            'primary_color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'logo_path' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $currentConfig = $tenant->config ?? [];
        $currentConfig['branding'] = array_merge($currentConfig['branding'] ?? [], $request->branding);

        $updateData = ['config' => $currentConfig];

        // Update tenant fields if provided
        if ($request->has('primary_color')) {
            $updateData['primary_color'] = $request->primary_color;
        }
        if ($request->has('secondary_color')) {
            $updateData['secondary_color'] = $request->secondary_color;
        }
        if ($request->has('logo_path')) {
            $updateData['logo_path'] = $request->logo_path;
        }

        $tenant->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Tenant branding updated successfully',
            'data' => [
                'tenant_id' => $tenant->id,
                'updated_branding' => $currentConfig['branding'],
                'primary_color' => $tenant->fresh()->primary_color,
                'secondary_color' => $tenant->fresh()->secondary_color,
                'logo_path' => $tenant->fresh()->logo_path,
                'updated_at' => $tenant->fresh()->updated_at,
            ]
        ]);
    }

    /**
     * Get tenant limits
     */
    public function getLimits(Tenant $tenant): JsonResponse
    {
        $limits = $tenant->config['limits'] ?? [];
        $usage = $this->calculateUsage($tenant);
        
        return response()->json([
            'success' => true,
            'data' => [
                'tenant_id' => $tenant->id,
                'limits' => $limits,
                'usage' => $usage,
                'percentage_used' => $this->calculatePercentageUsed($limits, $usage),
            ]
        ]);
    }

    /**
     * Update tenant limits
     */
    public function updateLimits(Request $request, Tenant $tenant): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limits' => 'required|array',
            'limits.max_users' => 'nullable|integer|min:1|max:1000',
            'limits.max_storage_mb' => 'nullable|integer|min:100|max:10240',
            'limits.max_items' => 'nullable|integer|min:1|max:10000',
            'limits.max_transactions_per_month' => 'nullable|integer|min:1|max:10000',
            'limits.max_events_per_month' => 'nullable|integer|min:1|max:1000',
            'limits.max_bookings_per_month' => 'nullable|integer|min:1|max:1000',
            'limits.max_customers' => 'nullable|integer|min:1|max:5000',
            'limits.max_invoices_per_month' => 'nullable|integer|min:1|max:1000',
            'limits.max_announcements_per_day' => 'nullable|integer|min:1|max:100',
            'limits.max_committees' => 'nullable|integer|min:1|max:100',
            'limits.max_inventory_items' => 'nullable|integer|min:1|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $currentConfig = $tenant->config ?? [];
        $currentConfig['limits'] = array_merge($currentConfig['limits'] ?? [], $request->limits);

        // Also update main config fields
        $updateData = ['config' => $currentConfig];
        if (isset($request->limits['max_users'])) {
            $currentConfig['max_users'] = $request->limits['max_users'];
            $updateData['config'] = $currentConfig;
        }
        if (isset($request->limits['max_storage_mb'])) {
            $currentConfig['max_storage_mb'] = $request->limits['max_storage_mb'];
            $updateData['config'] = $currentConfig;
        }

        $tenant->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Tenant limits updated successfully',
            'data' => [
                'tenant_id' => $tenant->id,
                'updated_limits' => $currentConfig['limits'],
                'updated_at' => $tenant->fresh()->updated_at,
            ]
        ]);
    }

    /**
     * Get current tenant configuration
     */
    public function getCurrentTenantConfig(Request $request): JsonResponse
    {
        $tenant = $this->tenantContext->getCurrentTenant();
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found'
            ], 404);
        }

        return $this->getConfig($tenant);
    }

    /**
     * Get current tenant features
     */
    public function getCurrentTenantFeatures(Request $request): JsonResponse
    {
        $tenant = $this->tenantContext->getCurrentTenant();
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found'
            ], 404);
        }

        return $this->getFeatures($tenant);
    }

    /**
     * Calculate tenant usage
     */
    private function calculateUsage(Tenant $tenant): array
    {
        return [
            'users_count' => $tenant->users()->count(),
            'active_users' => $tenant->users()->where('is_active', true)->count(),
            'storage_used_mb' => 0, // TODO: Implement storage calculation
            'items_count' => 0, // TODO: Implement items counting
            'transactions_this_month' => 0, // TODO: Implement transaction counting
            'events_this_month' => 0, // TODO: Implement event counting
            'bookings_this_month' => 0, // TODO: Implement booking counting
            'customers_count' => 0, // TODO: Implement customer counting
            'invoices_this_month' => 0, // TODO: Implement invoice counting
            'announcements_today' => 0, // TODO: Implement announcement counting
            'committees_count' => 0, // TODO: Implement committee counting
            'inventory_items_count' => 0, // TODO: Implement inventory counting
        ];
    }

    /**
     * Calculate percentage used for limits
     */
    private function calculatePercentageUsed(array $limits, array $usage): array
    {
        $percentages = [];
        
        $mappings = [
            'max_users' => 'users_count',
            'max_storage_mb' => 'storage_used_mb',
            'max_items' => 'items_count',
            'max_transactions_per_month' => 'transactions_this_month',
            'max_events_per_month' => 'events_this_month',
            'max_bookings_per_month' => 'bookings_this_month',
            'max_customers' => 'customers_count',
            'max_invoices_per_month' => 'invoices_this_month',
            'max_announcements_per_day' => 'announcements_today',
            'max_committees' => 'committees_count',
            'max_inventory_items' => 'inventory_items_count',
        ];

        foreach ($mappings as $limitKey => $usageKey) {
            if (isset($limits[$limitKey]) && isset($usage[$usageKey])) {
                $limit = $limits[$limitKey];
                $used = $usage[$usageKey];
                $percentages[$limitKey] = $limit > 0 ? round(($used / $limit) * 100, 2) : 0;
            }
        }

        return $percentages;
    }
}