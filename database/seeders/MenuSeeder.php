<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Get default tenant ID
        $defaultTenantId = config('seeding.default_tenant_id', 1);

        // Dashboard Menu (accessible by all authenticated users)
        Menu::create([
            'name' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'fa fa-dashboard',
            'url' => '/dashboard',
            'route' => 'dashboard',
            'parent_id' => null,
            'sort_order' => 1,
            'is_active' => true,
            'tenant_id' => $defaultTenantId,
            'permissions' => [
                ['type' => 'permission', 'name' => 'view dashboard']
            ]
        ]);

        // System Management Menu (only for admins)
        $systemMenu = Menu::create([
            'name' => 'system',
            'label' => 'System',
            'icon' => 'fa fa-cogs',
            'url' => null,
            'route' => null,
            'parent_id' => null,
            'sort_order' => 2,
            'is_active' => true,
            'tenant_id' => $defaultTenantId,
            'permissions' => [
                ['type' => 'role', 'name' => 'Admin']
            ]
        ]);

        // User Management (child of System)
        Menu::create([
            'name' => 'users',
            'label' => 'User',
            'icon' => 'fa fa-users',
            'url' => '/system/users',
            'route' => 'system.users.index',
            'parent_id' => $systemMenu->id,
            'sort_order' => 1,
            'is_active' => true,
            'tenant_id' => $defaultTenantId,
            'permissions' => [
                ['type' => 'permission', 'name' => 'view users']
            ]
        ]);

        // Role Management (child of System)
        Menu::create([
            'name' => 'roles',
            'label' => 'Role',
            'icon' => 'fa fa-user-circle',
            'url' => '/system/roles',
            'route' => 'system.roles.index',
            'parent_id' => $systemMenu->id,
            'sort_order' => 2,
            'is_active' => true,
            'tenant_id' => $defaultTenantId,
            'permissions' => [
                ['type' => 'permission', 'name' => 'view roles']
            ]
        ]);

        // Permission Management (child of System)
        Menu::create([
            'name' => 'permissions',
            'label' => 'Permission',
            'icon' => 'fa fa-shield',
            'url' => '/system/permissions',
            'route' => 'system.permissions.index',
            'parent_id' => $systemMenu->id,
            'sort_order' => 3,
            'is_active' => true,
            'tenant_id' => $defaultTenantId,
            'permissions' => [
                ['type' => 'permission', 'name' => 'view permissions']
            ]
        ]);

        // Menu Management (child of System)
        Menu::create([
            'name' => 'menus',
            'label' => 'Menu',
            'icon' => 'fa fa-bars',
            'url' => '/system/menus',
            'route' => 'system.menus.index',
            'parent_id' => $systemMenu->id,
            'sort_order' => 4,
            'is_active' => true,
            'tenant_id' => $defaultTenantId,
            'permissions' => [
                ['type' => 'permission', 'name' => 'view menus']
            ]
        ]);

        // Audit Logs (child of System - accessible by users too)
        Menu::create([
            'name' => 'audits',
            'label' => 'Audit Logs',
            'icon' => 'fa fa-history',
            'url' => '/system/audits',
            'route' => 'system.audits.index',
            'parent_id' => $systemMenu->id,
            'sort_order' => 5,
            'is_active' => true,
            'tenant_id' => $defaultTenantId,
            'permissions' => [
                ['type' => 'permission', 'name' => 'view audits']
            ]
        ]);

        // System Settings (child of System)
        Menu::create([
            'name' => 'settings',
            'label' => 'System Settings',
            'icon' => 'fa fa-gear',
            'url' => '/system/settings',
            'route' => 'system.settings.index',
            'parent_id' => $systemMenu->id,
            'sort_order' => 6,
            'is_active' => true,
            'tenant_id' => $defaultTenantId,
            'permissions' => [
                ['type' => 'permission', 'name' => 'view system settings']
            ]
        ]);

        // Tenant Management (child of System)
        Menu::create([
            'name' => 'tenants',
            'label' => 'Tenants',
            'icon' => 'fa fa-building',
            'url' => '/system/tenants',
            'route' => 'system.tenants.index',
            'parent_id' => $systemMenu->id,
            'sort_order' => 7,
            'is_active' => true,
            'tenant_id' => $defaultTenantId,
            'permissions' => [
                ['type' => 'permission', 'name' => 'view tenants']
            ]
        ]);

        $this->command->info('Menu structure created successfully.');
        $this->command->info('Total menus: ' . Menu::count());
        $this->command->line('Dashboard - accessible by: view dashboard permission');
        $this->command->line('System Management - accessible by: admin role only');
        $this->command->line('User/Role/Permission/Menu Management - accessible by: respective permissions');
        $this->command->line('Audit Logs - accessible by: view audits permission (admin + user)');
        $this->command->line('System Settings - accessible by: view system settings permission');
    }
}
