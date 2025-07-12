<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Menu;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Global search across all modules
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'results' => [],
                'message' => 'Query must be at least 2 characters long'
            ]);
        }

        $results = [];
        
        try {
            // Search Users
            if (auth()->user()->can('view users')) {
                $users = User::where('name', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%")
                    ->limit(5)
                    ->get();
                
                foreach ($users as $user) {
                    $results[] = [
                        'id' => $user->id,
                        'type' => 'users',
                        'title' => $user->name,
                        'description' => $user->email,
                        'url' => "/system/users/{$user->encrypted_id}/edit",
                        'badge' => [
                            'text' => $user->roles->first()?->name ?? 'No Role',
                            'class' => $user->roles->first()?->name === 'Admin' ? 'badge-primary' : 'badge-secondary'
                        ]
                    ];
                }
            }

            // Search Roles
            if (auth()->user()->can('view roles')) {
                $roles = Role::where('name', 'LIKE', "%{$query}%")
                    ->limit(5)
                    ->get();
                
                foreach ($roles as $role) {
                    $results[] = [
                        'id' => $role->id,
                        'type' => 'roles',
                        'title' => $role->name,
                        'description' => $role->permissions->count() . ' permissions assigned',
                        'url' => "/system/roles/{$role->encrypted_id}/edit",
                        'badge' => [
                            'text' => $role->permissions->count() . ' perms',
                            'class' => 'badge-info'
                        ]
                    ];
                }
            }

            // Search Permissions
            if (auth()->user()->can('view permissions')) {
                $permissions = Permission::where('name', 'LIKE', "%{$query}%")
                    ->orWhere('group', 'LIKE', "%{$query}%")
                    ->limit(5)
                    ->get();
                
                foreach ($permissions as $permission) {
                    $results[] = [
                        'id' => $permission->id,
                        'type' => 'permissions',
                        'title' => $permission->name,
                        'description' => $permission->group ? "Group: {$permission->group}" : 'No group',
                        'url' => "/system/permissions/{$permission->encrypted_id}/edit",
                        'badge' => [
                            'text' => $permission->group ?: 'General',
                            'class' => 'badge-warning'
                        ]
                    ];
                }
            }

            // Search Menus
            if (auth()->user()->can('view menus')) {
                $menus = Menu::where('name', 'LIKE', "%{$query}%")
                    ->orWhere('label', 'LIKE', "%{$query}%")
                    ->orWhere('url', 'LIKE', "%{$query}%")
                    ->orWhere('route', 'LIKE', "%{$query}%")
                    ->limit(5)
                    ->get();
                
                foreach ($menus as $menu) {
                    $results[] = [
                        'id' => $menu->id,
                        'type' => 'menus',
                        'title' => $menu->label,
                        'description' => $menu->url ?: $menu->route ?: 'No URL/Route',
                        'url' => "/system/menus/{$menu->encrypted_id}/edit",
                        'badge' => [
                            'text' => $menu->is_active ? 'Active' : 'Inactive',
                            'class' => $menu->is_active ? 'badge-success' : 'badge-secondary'
                        ]
                    ];
                }
            }

            // Search System Settings
            if (auth()->user()->can('view system settings')) {
                $settings = SystemSetting::where('name', 'LIKE', "%{$query}%")
                    ->orWhere('key', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%")
                    ->limit(5)
                    ->get();
                
                foreach ($settings as $setting) {
                    $results[] = [
                        'id' => $setting->id,
                        'type' => 'settings',
                        'title' => $setting->name,
                        'description' => $setting->description ?: $setting->key,
                        'url' => "/system/settings#{$setting->key}",
                        'badge' => [
                            'text' => ucfirst($setting->group ?: 'general'),
                            'class' => 'badge-dark'
                        ]
                    ];
                }
            }

            // Sort results by relevance (exact matches first)
            usort($results, function ($a, $b) use ($query) {
                $aScore = $this->calculateRelevanceScore($a, $query);
                $bScore = $this->calculateRelevanceScore($b, $query);
                return $bScore <=> $aScore;
            });

            return response()->json([
                'results' => $results,
                'total' => count($results),
                'query' => $query
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'results' => [],
                'error' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate relevance score for search results
     *
     * @param array $result
     * @param string $query
     * @return int
     */
    private function calculateRelevanceScore(array $result, string $query): int
    {
        $score = 0;
        $queryLower = strtolower($query);
        $titleLower = strtolower($result['title']);
        $descriptionLower = strtolower($result['description'] ?? '');
        
        // Exact title match
        if ($titleLower === $queryLower) {
            $score += 100;
        }
        
        // Title starts with query
        if (str_starts_with($titleLower, $queryLower)) {
            $score += 50;
        }
        
        // Title contains query
        if (str_contains($titleLower, $queryLower)) {
            $score += 25;
        }
        
        // Description contains query
        if (str_contains($descriptionLower, $queryLower)) {
            $score += 10;
        }
        
        return $score;
    }
}