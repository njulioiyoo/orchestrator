<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;

class TenantUserController extends Controller
{
    protected TenantContext $tenantContext;

    public function __construct(TenantContext $tenantContext)
    {
        $this->tenantContext = $tenantContext;
    }

    /**
     * Display a listing of tenant users
     */
    public function index(Request $request, Tenant $tenant): JsonResponse
    {
        $query = $tenant->users();

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Order by
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        $users = $query->paginate($request->get('per_page', 15));

        // Calculate usage
        $totalUsers = $tenant->users()->count();
        $activeUsers = $tenant->users()->where('is_active', true)->count();
        $maxUsers = $tenant->config['max_users'] ?? 10;

        return response()->json([
            'success' => true,
            'data' => [
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'business_type' => $tenant->business_type,
                ],
                'users' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'last_page' => $users->lastPage(),
                ],
                'usage' => [
                    'total_users' => $totalUsers,
                    'active_users' => $activeUsers,
                    'inactive_users' => $totalUsers - $activeUsers,
                    'max_users' => $maxUsers,
                    'percentage_used' => $maxUsers > 0 ? round(($totalUsers / $maxUsers) * 100, 2) : 0,
                    'available_slots' => max(0, $maxUsers - $totalUsers),
                ]
            ]
        ]);
    }

    /**
     * Store a new user in tenant
     */
    public function store(Request $request, Tenant $tenant): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', PasswordRule::defaults()],
            'is_active' => 'boolean',
            'profile' => 'nullable|array',
            'profile.phone' => 'nullable|string|max:20',
            'profile.position' => 'nullable|string|max:255',
            'profile.address' => 'nullable|string',
            'profile.birth_date' => 'nullable|date',
            'profile.join_date' => 'nullable|date',
            'profile.emergency_contact' => 'nullable|string|max:20',
            'profile.education' => 'nullable|string|max:255',
            'profile.experience' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check user limit
        $userCount = $tenant->users()->count();
        $maxUsers = $tenant->config['max_users'] ?? 10;

        if ($userCount >= $maxUsers) {
            return response()->json([
                'success' => false,
                'message' => 'User limit reached for this tenant',
                'data' => [
                    'current_users' => $userCount,
                    'max_users' => $maxUsers,
                ]
            ], 403);
        }

        // Create user
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => $request->get('is_active', true),
            'profile' => $request->profile ?? [],
            'permissions' => $request->permissions ?? [],
            'settings' => array_merge([
                'notification_email' => true,
                'notification_sms' => false,
                'dashboard_theme' => 'light',
                'language' => $tenant->locale ?? 'id',
                'timezone' => $tenant->timezone ?? 'Asia/Jakarta',
            ], $request->settings ?? []),
            'email_verified_at' => ($tenant->config['email_verification'] ?? true) ? null : now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => [
                'user' => $user,
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                ],
                'usage' => [
                    'total_users' => $tenant->users()->count(),
                    'max_users' => $maxUsers,
                    'available_slots' => max(0, $maxUsers - $tenant->users()->count()),
                ]
            ]
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(Tenant $tenant, User $user): JsonResponse
    {
        // Check if user belongs to tenant
        if ($user->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'User not found in this tenant'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                ],
                'permissions_count' => count($user->permissions ?? []),
                'last_login' => $user->last_login_at,
                'email_verified' => $user->email_verified_at !== null,
            ]
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, Tenant $tenant, User $user): JsonResponse
    {
        // Check if user belongs to tenant
        if ($user->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'User not found in this tenant'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['sometimes', PasswordRule::defaults()],
            'is_active' => 'sometimes|boolean',
            'profile' => 'sometimes|array',
            'profile.phone' => 'nullable|string|max:20',
            'profile.position' => 'nullable|string|max:255',
            'profile.address' => 'nullable|string',
            'profile.birth_date' => 'nullable|date',
            'profile.join_date' => 'nullable|date',
            'profile.emergency_contact' => 'nullable|string|max:20',
            'profile.education' => 'nullable|string|max:255',
            'profile.experience' => 'nullable|string',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'string',
            'settings' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['name', 'email', 'is_active']);

        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        if ($request->has('profile')) {
            $updateData['profile'] = array_merge($user->profile ?? [], $request->profile);
        }

        if ($request->has('permissions')) {
            $updateData['permissions'] = $request->permissions;
        }

        if ($request->has('settings')) {
            $updateData['settings'] = array_merge($user->settings ?? [], $request->settings);
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => [
                'user' => $user->fresh(),
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                ],
                'updated_at' => $user->fresh()->updated_at,
            ]
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy(Tenant $tenant, User $user): JsonResponse
    {
        // Check if user belongs to tenant
        if ($user->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'User not found in this tenant'
            ], 404);
        }

        // Prevent deletion of current user
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account'
            ], 403);
        }

        $userName = $user->name;
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => "User '{$userName}' deleted successfully",
            'data' => [
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                ],
                'remaining_users' => $tenant->users()->count(),
            ]
        ]);
    }

    /**
     * Update user status
     */
    public function updateStatus(Request $request, Tenant $tenant, User $user): JsonResponse
    {
        // Check if user belongs to tenant
        if ($user->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'User not found in this tenant'
            ], 404);
        }

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

        // Prevent deactivating current user
        if ($user->id === auth()->id() && !$request->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot deactivate your own account'
            ], 403);
        }

        $user->update(['is_active' => $request->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully',
            'data' => [
                'user' => $user->fresh(),
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                ],
                'status' => $request->is_active ? 'activated' : 'deactivated',
            ]
        ]);
    }

    /**
     * Update user permissions
     */
    public function updatePermissions(Request $request, Tenant $tenant, User $user): JsonResponse
    {
        // Check if user belongs to tenant
        if ($user->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'User not found in this tenant'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldPermissions = $user->permissions ?? [];
        $newPermissions = $request->permissions;

        // Track changes
        $added = array_diff($newPermissions, $oldPermissions);
        $removed = array_diff($oldPermissions, $newPermissions);

        $user->update(['permissions' => $newPermissions]);

        return response()->json([
            'success' => true,
            'message' => 'User permissions updated successfully',
            'data' => [
                'user' => $user->fresh(),
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                ],
                'permissions' => $newPermissions,
                'changes' => [
                    'added' => array_values($added),
                    'removed' => array_values($removed),
                    'total_permissions' => count($newPermissions),
                ],
                'updated_at' => $user->fresh()->updated_at,
            ]
        ]);
    }

    /**
     * Get current tenant users
     */
    public function getCurrentTenantUsers(Request $request): JsonResponse
    {
        $tenant = $this->tenantContext->getCurrentTenant();
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No tenant context found'
            ], 404);
        }

        return $this->index($request, $tenant);
    }
}