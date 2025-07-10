<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions with groups
        $permissions = [
            // User Management
            ['name' => 'view users', 'group' => 'User Management'],
            ['name' => 'create users', 'group' => 'User Management'],
            ['name' => 'edit users', 'group' => 'User Management'],
            ['name' => 'delete users', 'group' => 'User Management'],

            // Role Management
            ['name' => 'view roles', 'group' => 'Role Management'],
            ['name' => 'create roles', 'group' => 'Role Management'],
            ['name' => 'edit roles', 'group' => 'Role Management'],
            ['name' => 'delete roles', 'group' => 'Role Management'],

            // Permission Management
            ['name' => 'view permissions', 'group' => 'Permission Management'],
            ['name' => 'create permissions', 'group' => 'Permission Management'],
            ['name' => 'edit permissions', 'group' => 'Permission Management'],
            ['name' => 'delete permissions', 'group' => 'Permission Management'],

            // Dashboard
            ['name' => 'view dashboard', 'group' => 'Dashboard'],

            // System
            ['name' => 'view system', 'group' => 'System'],
            ['name' => 'manage system', 'group' => 'System'],
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Create roles and assign permissions
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::create(['name' => 'Admin']);
        $admin->givePermissionTo([
            'view dashboard',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view roles',
            'create roles',
            'edit roles',
            'view permissions'
        ]);

        $user = Role::create(['name' => 'User']);
        $user->givePermissionTo(['view dashboard']);

        // Create default super admin user
        $superAdminUser = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
        ]);
        $superAdminUser->assignRole('Super Admin');

        // Create default admin user
        $adminUser = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $adminUser->assignRole('Admin');
    }
}
