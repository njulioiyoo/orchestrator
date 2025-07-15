<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Tenant;
use Spatie\Permission\Models\Permission;

class CompleteMenuStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get default tenant
        $defaultTenant = Tenant::where('slug', 'default')->first();
        
        if (!$defaultTenant) {
            $this->command->error('Default tenant not found. Please run SuperAdminSeeder first.');
            return;
        }

        // Delete all existing menus to start fresh
        Menu::where('tenant_id', $defaultTenant->id)->delete();

        // Create all required permissions
        $permissions = [
            'view dashboard',
            'view users', 'create users', 'edit users', 'delete users',
            'view roles', 'create roles', 'edit roles', 'delete roles',
            'view permissions', 'create permissions', 'edit permissions', 'delete permissions',
            'view menus', 'create menus', 'edit menus', 'delete menus',
            'view audits', 'manage audits',
            'view settings', 'manage settings',
            'view tenants', 'create tenants', 'edit tenants', 'delete tenants', 'manage tenants',
            'super_admin'
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'tenant_id' => $defaultTenant->id,
            ], [
                'guard_name' => 'web',
            ]);
        }

        // 1. Dashboard Menu
        $dashboardMenu = Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'fa fa-dashboard',
            'url' => '/dashboard',
            'route' => 'dashboard',
            'parent_id' => null,
            'sort_order' => 1,
            'is_active' => true,
            'permissions' => [
                ['type' => 'permission', 'name' => 'view dashboard']
            ]
        ]);

        // 2. System Management Menu (Parent)
        $systemMenu = Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'system',
            'label' => 'System Management',
            'icon' => 'fa fa-cogs',
            'url' => null,
            'route' => null,
            'parent_id' => null,
            'sort_order' => 2,
            'is_active' => true,
            'permissions' => [
                ['type' => 'permission', 'name' => 'super_admin']
            ]
        ]);

        // 2.1 Users Management
        Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'users',
            'label' => 'Users',
            'icon' => 'fa fa-users',
            'url' => '/system/users',
            'route' => 'system.users.index',
            'parent_id' => $systemMenu->id,
            'sort_order' => 1,
            'is_active' => true,
            'permissions' => [
                ['type' => 'permission', 'name' => 'view users']
            ]
        ]);

        // 2.2 Roles Management
        Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'roles',
            'label' => 'Roles',
            'icon' => 'fa fa-user-circle',
            'url' => '/system/roles',
            'route' => 'system.roles.index',
            'parent_id' => $systemMenu->id,
            'sort_order' => 2,
            'is_active' => true,
            'permissions' => [
                ['type' => 'permission', 'name' => 'view roles']
            ]
        ]);

        // 2.3 Permissions Management
        Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'permissions',
            'label' => 'Permissions',
            'icon' => 'fa fa-shield',
            'url' => '/system/permissions',
            'route' => 'system.permissions.index',
            'parent_id' => $systemMenu->id,
            'sort_order' => 3,
            'is_active' => true,
            'permissions' => [
                ['type' => 'permission', 'name' => 'view permissions']
            ]
        ]);

        // 2.4 Menu Management
        Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'menus',
            'label' => 'Menus',
            'icon' => 'fa fa-bars',
            'url' => '/system/menus',
            'route' => 'system.menus.index',
            'parent_id' => $systemMenu->id,
            'sort_order' => 4,
            'is_active' => true,
            'permissions' => [
                ['type' => 'permission', 'name' => 'view menus']
            ]
        ]);

        // 2.5 Audit Logs
        Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'audits',
            'label' => 'Audit Logs',
            'icon' => 'fa fa-history',
            'url' => '/system/audits',
            'route' => 'system.audits.index',
            'parent_id' => $systemMenu->id,
            'sort_order' => 5,
            'is_active' => true,
            'permissions' => [
                ['type' => 'permission', 'name' => 'view audits']
            ]
        ]);

        // 2.6 System Settings
        Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'settings',
            'label' => 'Settings',
            'icon' => 'fa fa-gear',
            'url' => '/system/settings',
            'route' => 'system.settings.index',
            'parent_id' => $systemMenu->id,
            'sort_order' => 6,
            'is_active' => true,
            'permissions' => [
                ['type' => 'permission', 'name' => 'view settings']
            ]
        ]);

        // 3. Tenant Management Menu (Parent)
        $tenantMenu = Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'tenant-management',
            'label' => 'Tenant Management',
            'icon' => 'fa fa-building',
            'url' => null,
            'route' => null,
            'parent_id' => null,
            'sort_order' => 3,
            'is_active' => true,
            'permissions' => [
                ['type' => 'permission', 'name' => 'manage tenants']
            ]
        ]);

        // 3.1 All Tenants
        Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'all-tenants',
            'label' => 'All Tenants',
            'icon' => 'fa fa-list',
            'url' => '/system/tenants',
            'route' => 'system.tenants.index',
            'parent_id' => $tenantMenu->id,
            'sort_order' => 1,
            'is_active' => true,
            'permissions' => [
                ['type' => 'permission', 'name' => 'view tenants']
            ]
        ]);

        // 3.2 Create Tenant
        Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'create-tenant',
            'label' => 'Create Tenant',
            'icon' => 'fa fa-plus',
            'url' => '/system/tenants/create',
            'route' => 'system.tenants.create',
            'parent_id' => $tenantMenu->id,
            'sort_order' => 2,
            'is_active' => true,
            'permissions' => [
                ['type' => 'permission', 'name' => 'create tenants']
            ]
        ]);

        // 3.3 Tenant Analytics
        Menu::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'tenant-analytics',
            'label' => 'Tenant Analytics',
            'icon' => 'fa fa-chart-bar',
            'url' => '/system/tenants/analytics',
            'route' => 'system.tenants.analytics',
            'parent_id' => $tenantMenu->id,
            'sort_order' => 3,
            'is_active' => true,
            'permissions' => [
                ['type' => 'permission', 'name' => 'view tenants']
            ]
        ]);

        $this->command->info('Complete menu structure created successfully!');
        $this->command->info('Created menus:');
        $this->command->info('- Dashboard');
        $this->command->info('- System Management (Users, Roles, Permissions, Menus, Audits, Settings)');
        $this->command->info('- Tenant Management (All Tenants, Create Tenant, Analytics)');
    }
}