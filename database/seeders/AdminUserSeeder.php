<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Get default tenant ID
        $defaultTenantId = config('seeding.default_tenant_id', 1);

        // Create admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@orchestrator.local'],
            [
                'name' => 'Admin',
                'email' => 'admin@orchestrator.local',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'tenant_id' => $defaultTenantId,
            ]
        );

        // Create regular user for testing
        $regularUser = User::firstOrCreate(
            ['email' => 'user@orchestrator.local'],
            [
                'name' => 'User',
                'email' => 'user@orchestrator.local', 
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'tenant_id' => $defaultTenantId,
            ]
        );

        // Assign roles
        $adminRole = Role::where('name', 'Admin')->first();
        $userRole = Role::where('name', 'User')->first();

        if ($adminRole) {
            $adminUser->assignRole($adminRole);
            $this->command->info("Assigned admin role to {$adminUser->name}");
        }

        if ($userRole) {
            $regularUser->assignRole($userRole);
            $this->command->info("Assigned user role to {$regularUser->name}");
        }

        // Display credentials
        $this->command->line('');
        $this->command->line('=== LOGIN CREDENTIALS ===');
        $this->command->line('Admin User:');
        $this->command->line("Email: admin@orchestrator.local");
        $this->command->line("Password: password");
        $this->command->line("Permissions: " . ($adminUser->getAllPermissions()->count() ?? 0));
        $this->command->line('');
        $this->command->line('Regular User:');
        $this->command->line("Email: user@orchestrator.local");
        $this->command->line("Password: password");
        $this->command->line("Permissions: " . ($regularUser->getAllPermissions()->count() ?? 0));
    }
}