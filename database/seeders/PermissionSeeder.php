<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Create permissions
        $permissions = [
            // User permissions
            ['name' => 'view users', 'group' => 'User Management'],
            ['name' => 'create users', 'group' => 'User Management'],
            ['name' => 'edit users', 'group' => 'User Management'],
            ['name' => 'delete users', 'group' => 'User Management'],
            
            // Role permissions
            ['name' => 'view roles', 'group' => 'Role Management'],
            ['name' => 'create roles', 'group' => 'Role Management'],
            ['name' => 'edit roles', 'group' => 'Role Management'],
            ['name' => 'delete roles', 'group' => 'Role Management'],
            
            // Permission permissions
            ['name' => 'view permissions', 'group' => 'Permission Management'],
            ['name' => 'create permissions', 'group' => 'Permission Management'],
            ['name' => 'edit permissions', 'group' => 'Permission Management'],
            ['name' => 'delete permissions', 'group' => 'Permission Management'],
            
            // Dashboard permissions
            ['name' => 'view dashboard', 'group' => 'Dashboard'],
            
            // System permissions
            ['name' => 'manage system', 'group' => 'System'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission['name'],
                'guard_name' => 'web'
            ], $permission);
        }

        // Create roles (only Admin and User)
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $user = Role::firstOrCreate(['name' => 'User']);

        // Assign permissions to roles
        $admin->givePermissionTo([
            'view dashboard',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
            'manage system',
        ]);
        
        $user->givePermissionTo([
            'view dashboard',
        ]);

        $this->command->info('Permissions and roles created successfully.');
    }
}