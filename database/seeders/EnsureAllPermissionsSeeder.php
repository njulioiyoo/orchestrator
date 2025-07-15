<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class EnsureAllPermissionsSeeder extends Seeder
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

        // All permissions needed for system functionality
        $permissions = [
            // Dashboard
            'view dashboard',
            
            // Users
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Roles
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            
            // Permissions
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
            
            // Menus
            'view menus',
            'create menus',
            'edit menus',
            'delete menus',
            'manage menus',
            
            // Audits
            'view audits',
            'manage audits',
            
            // Settings
            'view settings',
            'manage settings',
            'view system settings',
            
            // Tenants
            'view tenants',
            'create tenants',
            'edit tenants',
            'delete tenants',
            'manage tenants',
            'view all tenants',
            'switch tenants',
            
            // Super admin
            'super_admin',
            
            // General
            'manage_users',
            'manage_roles',
            'manage_permissions',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'tenant_id' => $defaultTenant->id,
            ], [
                'guard_name' => 'web',
            ]);
        }

        // Get or create the super-admin role
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'tenant_id' => $defaultTenant->id,
        ], [
            'guard_name' => 'web',
        ]);

        // Give all permissions to super-admin role
        $superAdminRole->syncPermissions($permissions);

        // Get super admin user and ensure permissions
        $superAdmin = User::where('email', 'admin@orchestrator.com')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions($permissions);
            if (!$superAdmin->hasRole('super-admin')) {
                $superAdmin->assignRole($superAdminRole);
            }
        }

        $this->command->info('All permissions ensured successfully!');
        $this->command->info('Total permissions created: ' . count($permissions));
        $this->command->info('Super admin role has all permissions.');
    }
}