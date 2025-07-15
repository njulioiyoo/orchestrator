<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Tenant;
use Spatie\Permission\Models\Permission;

class TenantMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultTenant = Tenant::where('slug', 'default')->first();
        
        if (!$defaultTenant) {
            $this->command->error('Default tenant not found. Please run SuperAdminSeeder first.');
            return;
        }

        // Create tenant management permission
        Permission::firstOrCreate([
            'name' => 'manage_tenants',
            'tenant_id' => $defaultTenant->id,
        ], [
            'guard_name' => 'web',
        ]);

        // Create tenant menu
        $tenantMenu = Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'tenant-management',
            'label' => 'Tenant Management',
            'url' => '/system/tenants',
            'icon' => 'fas fa-building',
            'permissions' => [
                ['type' => 'permission', 'name' => 'manage_tenants']
            ],
            'parent_id' => null,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Create sub-menus for tenant management
        Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'all-tenants',
            'label' => 'All Tenants',
            'url' => '/system/tenants',
            'icon' => 'fas fa-list',
            'permissions' => [
                ['type' => 'permission', 'name' => 'manage_tenants']
            ],
            'parent_id' => $tenantMenu->id,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'create-tenant',
            'label' => 'Create Tenant',
            'url' => '/system/tenants/create',
            'icon' => 'fas fa-plus',
            'permissions' => [
                ['type' => 'permission', 'name' => 'manage_tenants']
            ],
            'parent_id' => $tenantMenu->id,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'tenant-analytics',
            'label' => 'Tenant Analytics',
            'url' => '/system/tenants/analytics',
            'icon' => 'fas fa-chart-bar',
            'permissions' => [
                ['type' => 'permission', 'name' => 'manage_tenants']
            ],
            'parent_id' => $tenantMenu->id,
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // Create system menu for other admin functions
        $systemMenu = Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'system',
            'label' => 'System',
            'url' => '#',
            'icon' => 'fas fa-cogs',
            'permissions' => [
                ['type' => 'permission', 'name' => 'super_admin']
            ],
            'parent_id' => null,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'system-users',
            'label' => 'Users',
            'url' => '/system/users',
            'icon' => 'fas fa-users',
            'permissions' => [
                ['type' => 'permission', 'name' => 'manage_users']
            ],
            'parent_id' => $systemMenu->id,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'roles-permissions',
            'label' => 'Roles & Permissions',
            'url' => '/system/roles',
            'icon' => 'fas fa-user-shield',
            'permissions' => [
                ['type' => 'permission', 'name' => 'manage_roles']
            ],
            'parent_id' => $systemMenu->id,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'system-settings',
            'label' => 'Settings',
            'url' => '/system/settings',
            'icon' => 'fas fa-cog',
            'permissions' => [
                ['type' => 'permission', 'name' => 'manage_settings']
            ],
            'parent_id' => $systemMenu->id,
            'sort_order' => 3,
            'is_active' => true,
        ]);

        Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'audit-logs',
            'label' => 'Audit Logs',
            'url' => '/system/audits',
            'icon' => 'fas fa-history',
            'permissions' => [
                ['type' => 'permission', 'name' => 'manage_audits']
            ],
            'parent_id' => $systemMenu->id,
            'sort_order' => 4,
            'is_active' => true,
        ]);

        $this->command->info('Tenant management menus created successfully!');
    }
}