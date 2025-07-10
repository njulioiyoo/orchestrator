<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // First run the permission seeder to ensure permissions exist
        $this->call(PermissionSeeder::class);
        
        // Find or create the user
        $user = User::where('name', 'njulioiyoo')
                   ->orWhere('email', 'njulioiyoo@example.com')
                   ->first();
        
        if (!$user) {
            // If user doesn't exist, try to find any user with similar name
            $user = User::where('name', 'LIKE', '%julio%')->first();
        }
        
        if (!$user) {
            $this->command->error('User njulioiyoo not found in database!');
            $this->command->info('Available users:');
            User::all()->each(function ($u) {
                $this->command->line("- {$u->name} ({$u->email})");
            });
            return;
        }
        
        // Ensure Super Admin role exists with all permissions
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web'
        ]);
        
        // Give all permissions to Super Admin role
        $permissions = Permission::all();
        if ($permissions->count() > 0) {
            $superAdminRole->givePermissionTo($permissions);
            $this->command->info("Assigned {$permissions->count()} permissions to Super Admin role.");
        }
        
        // Assign Super Admin role to user
        if (!$user->hasRole('Super Admin')) {
            $user->assignRole('Super Admin');
            $this->command->info("Assigned Super Admin role to {$user->name}");
        } else {
            $this->command->info("{$user->name} already has Super Admin role");
        }
        
        // Display user info
        $this->command->line('');
        $this->command->line("User Information:");
        $this->command->line("Name: {$user->name}");
        $this->command->line("Email: {$user->email}");
        $this->command->line("Roles: " . implode(', ', $user->getRoleNames()->toArray()));
        $this->command->line("Total Permissions: " . $user->getAllPermissions()->count());
    }
}