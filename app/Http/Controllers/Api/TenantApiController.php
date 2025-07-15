<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TenantApiController extends Controller
{
    protected TenantContext $tenantContext;

    public function __construct(TenantContext $tenantContext)
    {
        $this->tenantContext = $tenantContext;
    }

    /**
     * Display a listing of tenants (admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tenant::query();
        
        // Filter by business type
        if ($request->has('business_type')) {
            $query->where('business_type', $request->business_type);
        }
        
        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        
        // Search by name or slug
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }
        
        $tenants = $query->paginate($request->get('per_page', 15));
        
        return response()->json([
            'success' => true,
            'data' => $tenants->items(),
            'pagination' => [
                'current_page' => $tenants->currentPage(),
                'per_page' => $tenants->perPage(),
                'total' => $tenants->total(),
                'last_page' => $tenants->lastPage(),
            ]
        ]);
    }

    /**
     * Store a new tenant
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug',
            'domain' => 'nullable|string|max:255|unique:tenants,domain',
            'subdomain' => 'nullable|string|max:255|unique:tenants,subdomain',
            'business_type' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'timezone' => 'nullable|string|max:50',
            'locale' => 'nullable|string|max:10',
            'currency' => 'nullable|string|max:3',
            'config' => 'nullable|array',
            'expires_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            $tenant = Tenant::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'domain' => $request->domain,
                'subdomain' => $request->subdomain,
                'business_type' => $request->business_type,
                'phone' => $request->phone,
                'address' => $request->address,
                'primary_color' => $request->primary_color ?? '#3B82F6',
                'secondary_color' => $request->secondary_color ?? '#1E40AF',
                'timezone' => $request->timezone ?? 'Asia/Jakarta',
                'locale' => $request->locale ?? 'id_ID',
                'currency' => $request->currency ?? 'IDR',
                'config' => $request->config ?? $this->getDefaultConfig(),
                'is_active' => true,
                'expires_at' => $request->expires_at,
            ]);

            // Create default admin user
            $adminUser = User::create([
                'tenant_id' => $tenant->id,
                'name' => 'Admin ' . $tenant->name,
                'email' => 'admin@' . ($tenant->domain ?? $tenant->slug . '.com'),
                'password' => bcrypt($tempPassword = Str::random(10)),
                'is_active' => true,
                'profile' => [
                    'position' => 'Administrator',
                    'phone' => $tenant->phone,
                    'address' => $tenant->address,
                ],
                'permissions' => [
                    'manage_users',
                    'manage_roles',
                    'manage_settings',
                    'view_reports',
                ],
                'settings' => [
                    'notification_email' => true,
                    'dashboard_theme' => 'light',
                    'language' => 'id',
                    'timezone' => $tenant->timezone,
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tenant created successfully',
                'data' => [
                    'tenant' => $tenant,
                    'default_admin' => [
                        'id' => $adminUser->id,
                        'name' => $adminUser->name,
                        'email' => $adminUser->email,
                        'temporary_password' => $tempPassword,
                        'message' => 'Please change password on first login'
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tenant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified tenant
     */
    public function show(Tenant $tenant): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'tenant' => $tenant,
                'users_count' => $tenant->users()->count(),
                'created_at' => $tenant->created_at,
                'updated_at' => $tenant->updated_at,
            ]
        ]);
    }

    /**
     * Update the specified tenant
     */
    public function update(Request $request, Tenant $tenant): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:tenants,slug,' . $tenant->id,
            'domain' => 'nullable|string|max:255|unique:tenants,domain,' . $tenant->id,
            'subdomain' => 'nullable|string|max:255|unique:tenants,subdomain,' . $tenant->id,
            'business_type' => 'sometimes|required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'timezone' => 'nullable|string|max:50',
            'locale' => 'nullable|string|max:10',
            'currency' => 'nullable|string|max:3',
            'config' => 'nullable|array',
            'expires_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $tenant->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tenant updated successfully',
            'data' => $tenant->fresh()
        ]);
    }

    /**
     * Remove the specified tenant
     */
    public function destroy(Tenant $tenant): JsonResponse
    {
        if ($tenant->slug === 'default') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete default tenant'
            ], 403);
        }

        $tenant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tenant deleted successfully'
        ]);
    }

    /**
     * Switch tenant context
     */
    public function switchTenant(Request $request, Tenant $tenant): JsonResponse
    {
        $user = $request->user();
        
        // Check if user has access to this tenant
        if ($user->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied to this tenant'
            ], 403);
        }

        // Update session tenant context
        session(['tenant_id' => $tenant->id]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant context switched successfully',
            'data' => [
                'current_tenant' => $tenant,
                'user_permissions' => $user->permissions ?? [],
                'session_updated' => true
            ]
        ]);
    }

    /**
     * Get tenant statistics
     */
    public function getTenantStats(Tenant $tenant): JsonResponse
    {
        $stats = [
            'users_count' => $tenant->users()->count(),
            'active_users' => $tenant->users()->where('is_active', true)->count(),
            'inactive_users' => $tenant->users()->where('is_active', false)->count(),
            'recent_logins' => $tenant->users()
                ->whereNotNull('last_login_at')
                ->where('last_login_at', '>=', now()->subDays(7))
                ->count(),
            'config' => $tenant->config,
            'limits' => $tenant->config['limits'] ?? [],
            'usage' => [
                'storage_used' => 0, // TODO: implement storage calculation
                'features_enabled' => count(array_filter($tenant->config['features'] ?? [])),
            ],
            'created_at' => $tenant->created_at,
            'expires_at' => $tenant->expires_at,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get current tenant info
     */
    public function getCurrentTenant(Request $request): JsonResponse
    {
        $tenant = $this->tenantContext->getCurrentTenant();
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tenant' => $tenant,
                'user' => $request->user(),
                'features' => $tenant->config['features'] ?? [],
                'branding' => $tenant->config['branding'] ?? [],
            ]
        ]);
    }

    /**
     * Get current tenant stats
     */
    public function getCurrentTenantStats(Request $request): JsonResponse
    {
        $tenant = $this->tenantContext->getCurrentTenant();
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found'
            ], 404);
        }

        return $this->getTenantStats($tenant);
    }

    /**
     * Resolve tenant from request
     */
    public function resolveTenant(Request $request): JsonResponse
    {
        $domain = $request->get('domain');
        $subdomain = $request->get('subdomain');
        $slug = $request->get('slug');

        $tenant = null;

        if ($domain) {
            $tenant = Tenant::where('domain', $domain)->where('is_active', true)->first();
        } elseif ($subdomain) {
            $tenant = Tenant::where('subdomain', $subdomain)->where('is_active', true)->first();
        } elseif ($slug) {
            $tenant = Tenant::where('slug', $slug)->where('is_active', true)->first();
        }

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tenant' => $tenant,
                'features' => $tenant->config['features'] ?? [],
                'branding' => $tenant->config['branding'] ?? [],
            ]
        ]);
    }

    /**
     * Get tenant info by slug
     */
    public function getTenantInfo(string $slug): JsonResponse
    {
        $tenant = Tenant::where('slug', $slug)->where('is_active', true)->first();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'business_type' => $tenant->business_type,
                    'domain' => $tenant->domain,
                    'subdomain' => $tenant->subdomain,
                ],
                'branding' => $tenant->config['branding'] ?? [],
                'features' => array_keys(array_filter($tenant->config['features'] ?? [])),
                'registration_allowed' => $tenant->config['allow_registration'] ?? false,
            ]
        ]);
    }

    /**
     * Update tenant status
     */
    public function updateStatus(Request $request, Tenant $tenant): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $tenant->update(['is_active' => $request->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant status updated successfully',
            'data' => $tenant->fresh()
        ]);
    }

    /**
     * Get default configuration for new tenant
     */
    private function getDefaultConfig(): array
    {
        return [
            'allow_registration' => false,
            'email_verification' => true,
            'two_factor_auth' => false,
            'max_users' => 10,
            'max_storage_mb' => 512,
            'features' => [
                'user_management' => true,
                'role_management' => true,
                'permission_management' => true,
                'financial_reports' => false,
            ],
            'branding' => [
                'app_name' => 'Management System',
                'tagline' => 'Your Business Management Solution',
                'favicon' => null,
                'custom_css' => null,
            ],
            'notification_settings' => [
                'email_notifications' => true,
                'sms_notifications' => false,
            ],
            'limits' => [
                'max_items' => 100,
                'max_transactions_per_month' => 50,
            ]
        ];
    }
}