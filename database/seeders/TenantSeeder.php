<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default tenant
        $defaultTenant = Tenant::create([
            'name' => 'Default Tenant',
            'slug' => 'default',
            'domain' => null,
            'subdomain' => null,
            'config' => [
                'allow_registration' => true,
                'email_verification' => false,
                'two_factor_auth' => false,
                'max_users' => 100,
                'features' => [
                    'users' => true,
                    'roles' => true,
                    'permissions' => true,
                    'audits' => true,
                    'menus' => true,
                    'settings' => true,
                ]
            ],
            'is_active' => true,
            'expires_at' => null,
        ]);

        // Create demo tenant
        Tenant::create([
            'name' => 'Demo Company',
            'slug' => 'demo',
            'domain' => 'demo.example.com',
            'subdomain' => 'demo',
            'config' => [
                'allow_registration' => false,
                'email_verification' => true,
                'two_factor_auth' => true,
                'max_users' => 50,
                'features' => [
                    'users' => true,
                    'roles' => true,
                    'permissions' => true,
                    'audits' => true,
                    'menus' => true,
                    'settings' => false,
                ]
            ],
            'is_active' => true,
            'expires_at' => now()->addYear(),
        ]);

        // Store default tenant ID for other seeders
        config(['seeding.default_tenant_id' => $defaultTenant->id]);
    }
}