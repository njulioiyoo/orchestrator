<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Get default tenant ID
        $defaultTenantId = config('seeding.default_tenant_id', 1);

        // Create permissions based on application modules
        $permissions = [
            // Dashboard Module
            ['name' => 'view dashboard', 'group' => 'Dashboard'],
            
            // User Management Module
            ['name' => 'view users', 'group' => 'User Management'],
            ['name' => 'create users', 'group' => 'User Management'],
            ['name' => 'edit users', 'group' => 'User Management'],
            ['name' => 'delete users', 'group' => 'User Management'],
            
            // Role Management Module
            ['name' => 'view roles', 'group' => 'Role Management'],
            ['name' => 'create roles', 'group' => 'Role Management'],
            ['name' => 'edit roles', 'group' => 'Role Management'],
            ['name' => 'delete roles', 'group' => 'Role Management'],
            
            // Permission Management Module
            ['name' => 'view permissions', 'group' => 'Permission Management'],
            ['name' => 'create permissions', 'group' => 'Permission Management'],
            ['name' => 'edit permissions', 'group' => 'Permission Management'],
            ['name' => 'delete permissions', 'group' => 'Permission Management'],
            
            // Menu Management Module
            ['name' => 'view menus', 'group' => 'Menu Management'],
            ['name' => 'create menus', 'group' => 'Menu Management'],
            ['name' => 'edit menus', 'group' => 'Menu Management'],
            ['name' => 'delete menus', 'group' => 'Menu Management'],
            ['name' => 'update menu order', 'group' => 'Menu Management'],
            
            // Audit Management Module
            ['name' => 'view audits', 'group' => 'Audit Management'],
            ['name' => 'view audit details', 'group' => 'Audit Management'],
            
            // System Settings Module
            ['name' => 'view system settings', 'group' => 'System Settings'],
            ['name' => 'edit system settings', 'group' => 'System Settings'],
            
            // Tenant Management Module
            ['name' => 'view tenants', 'group' => 'Tenant Management'],
            ['name' => 'create tenants', 'group' => 'Tenant Management'],
            ['name' => 'edit tenants', 'group' => 'Tenant Management'],
            ['name' => 'delete tenants', 'group' => 'Tenant Management'],
            ['name' => 'switch tenants', 'group' => 'Tenant Management'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission['name'],
                'guard_name' => 'web'
            ], array_merge($permission, ['tenant_id' => $defaultTenantId]));
        }

        // Create roles - only Admin and User
        $admin = Role::firstOrCreate([
            'name' => 'Admin', 
            'guard_name' => 'web'
        ], [
            'name' => 'Admin',
            'guard_name' => 'web',
            'tenant_id' => $defaultTenantId
        ]);
        
        $user = Role::firstOrCreate([
            'name' => 'User', 
            'guard_name' => 'web'
        ], [
            'name' => 'User',
            'guard_name' => 'web',
            'tenant_id' => $defaultTenantId
        ]);

        // Admin gets ALL permissions
        $adminPermissions = [
            'view dashboard',
            'view users', 'create users', 'edit users', 'delete users',
            'view roles', 'create roles', 'edit roles', 'delete roles', 
            'view permissions', 'create permissions', 'edit permissions', 'delete permissions',
            'view menus', 'create menus', 'edit menus', 'delete menus', 'update menu order',
            'view audits', 'view audit details',
            'view system settings', 'edit system settings',
            'view tenants', 'create tenants', 'edit tenants', 'delete tenants', 'switch tenants'
        ];
        
        // User gets limited permissions
        $userPermissions = [
            'view dashboard',
            'view audits', // Can view their own audit logs
        ];

        // Sync permissions (removes old ones, adds new ones)
        $admin->syncPermissions($adminPermissions);
        $user->syncPermissions($userPermissions);

        $this->command->info('Permissions and roles created successfully.');
        $this->command->info('Admin role: ' . count($adminPermissions) . ' permissions');
        $this->command->info('User role: ' . count($userPermissions) . ' permissions');
    }
}