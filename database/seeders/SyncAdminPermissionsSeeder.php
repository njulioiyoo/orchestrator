<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SyncAdminPermissionsSeeder extends Seeder
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

        // Get super admin user
        $superAdmin = User::where('email', 'admin@orchestrator.com')->first();
        
        if (!$superAdmin) {
            $this->command->error('Super admin user not found. Please run SuperAdminSeeder first.');
            return;
        }

        // Get or create the super-admin role
        $adminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'tenant_id' => $defaultTenant->id,
        ], [
            'guard_name' => 'web',
        ]);

        // Define all permissions
        $permissions = [
            'manage_users',
            'manage_roles',
            'manage_permissions',
            'manage_tenants',
            'manage_settings',
            'view_all_tenants',
            'switch_tenants',
            'manage_audits',
            'manage_menus',
            'super_admin',
        ];

        // Create or update permissions
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'tenant_id' => $defaultTenant->id,
            ], [
                'guard_name' => 'web',
            ]);
        }

        // Assign all permissions to the admin role
        $adminRole->givePermissionTo($permissions);

        // Make sure user has the role
        if (!$superAdmin->hasRole('super-admin')) {
            $superAdmin->assignRole($adminRole);
        }

        // Also sync permissions to user directly
        $superAdmin->givePermissionTo($permissions);

        $this->command->info('Admin permissions synced successfully!');
        $this->command->info('User now has role: ' . $superAdmin->getRoleNames()->implode(', '));
        $this->command->info('User now has permissions: ' . $superAdmin->getAllPermissions()->pluck('name')->implode(', '));
    }
}