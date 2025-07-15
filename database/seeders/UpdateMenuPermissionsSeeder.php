<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UpdateMenuPermissionsSeeder extends Seeder
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

        // Create missing permissions
        $permissions = [
            'view dashboard',
            'view users',
            'view roles',
            'view permissions',
            'view menus',
            'view audits',
            'view system settings',
            'view tenants'
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
        $superAdminRole->givePermissionTo($permissions);

        // Update menus to use super-admin role instead of Admin role
        Menu::where('permissions', 'like', '%Admin%')->update([
            'permissions' => [
                ['type' => 'role', 'name' => 'super-admin']
            ]
        ]);

        // Get super admin user and assign permissions
        $superAdmin = User::where('email', 'admin@orchestrator.com')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        $this->command->info('Menu permissions updated successfully!');
        $this->command->info('All menus now use super-admin role or appropriate permissions.');
    }
}