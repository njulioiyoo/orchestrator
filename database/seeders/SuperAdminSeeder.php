<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get default tenant
        $defaultTenant = Tenant::where('slug', 'default')->first();
        
        if (!$defaultTenant) {
            $defaultTenant = Tenant::create([
                'name' => 'Default Tenant',
                'slug' => 'default',
                'domain' => null,
                'subdomain' => null,
                'business_type' => 'system',
                'config' => [
                    'allow_registration' => true,
                    'email_verification' => false,
                    'two_factor_auth' => false,
                    'max_users' => 1000,
                    'max_storage_mb' => 10240,
                    'features' => [
                        'users' => true,
                        'roles' => true,
                        'permissions' => true,
                        'audits' => true,
                        'menus' => true,
                        'settings' => true,
                        'tenants' => true,
                        'super_admin' => true,
                    ]
                ],
                'is_active' => true,
                'expires_at' => null,
            ]);
        }

        // Create super admin user
        $superAdmin = User::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'Super Admin',
            'email' => 'admin@orchestrator.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'profile' => [
                'phone' => '081234567890',
                'position' => 'Super Administrator',
                'address' => 'System Administrator',
                'birth_date' => '1990-01-01',
                'join_date' => now()->format('Y-m-d'),
                'emergency_contact' => '081234567891',
                'education' => 'System Administrator',
                'experience' => 'Full system access',
            ],
            'user_permissions' => [
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
            ],
            'settings' => [
                'notification_email' => true,
                'notification_sms' => false,
                'dashboard_theme' => 'light',
                'language' => 'id',
                'timezone' => 'Asia/Jakarta',
                'can_access_all_tenants' => true,
            ],
            'email_verified_at' => now(),
        ]);

        // Create admin role if not exists
        $adminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'tenant_id' => $defaultTenant->id,
        ], [
            'guard_name' => 'web',
        ]);

        // Create permissions for admin
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

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'tenant_id' => $defaultTenant->id,
            ], [
                'guard_name' => 'web',
            ]);
        }

        // Assign role to admin
        $superAdmin->assignRole($adminRole);

        // Give all permissions to admin role
        $adminRole->givePermissionTo($permissions);

        // Create regular admin user for web access
        $webAdmin = User::create([
            'tenant_id' => $defaultTenant->id,
            'name' => 'Web Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'profile' => [
                'phone' => '081234567892',
                'position' => 'Web Administrator',
                'address' => 'Web Administrator',
                'birth_date' => '1990-01-01',
                'join_date' => now()->format('Y-m-d'),
                'emergency_contact' => '081234567893',
                'education' => 'Web Administrator',
                'experience' => 'Web system access',
            ],
            'user_permissions' => [
                'manage_users',
                'manage_roles',
                'manage_permissions',
                'manage_tenants',
                'manage_settings',
                'view_all_tenants',
                'switch_tenants',
                'manage_audits',
                'manage_menus',
            ],
            'settings' => [
                'notification_email' => true,
                'notification_sms' => false,
                'dashboard_theme' => 'light',
                'language' => 'id',
                'timezone' => 'Asia/Jakarta',
                'can_access_all_tenants' => true,
            ],
            'email_verified_at' => now(),
        ]);

        // Assign role to web admin
        $webAdmin->assignRole($adminRole);

        $this->command->info('Super admin and web admin created successfully!');
        $this->command->info('Super Admin: admin@orchestrator.com / password');
        $this->command->info('Web Admin: admin@admin.com / password');
        $this->command->info('Default Tenant ID: ' . $defaultTenant->id);
    }
}