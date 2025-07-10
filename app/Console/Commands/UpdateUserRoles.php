<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UpdateUserRoles extends Command
{
    protected $signature = 'roles:update-users';
    protected $description = 'Update existing users to have proper roles (Admin or User)';

    public function handle()
    {
        $this->info('Updating user roles...');
        
        // First, run the seeder to ensure we have proper roles
        $this->call('db:seed', ['--class' => 'PermissionSeeder']);
        
        // Remove old Super Admin role if exists
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole) {
            $superAdminRole->delete();
            $this->info('Removed Super Admin role');
        }
        
        // Get all users
        $users = User::all();
        
        foreach ($users as $user) {
            // Remove all existing roles first
            $user->roles()->detach();
            
            // Assign roles based on user name
            if ($user->name === 'njulioiyoo') {
                $user->assignRole('Admin');
                $this->info("Assigned Admin role to {$user->name}");
            } else {
                $user->assignRole('User');
                $this->info("Assigned User role to {$user->name}");
            }
        }
        
        $this->info('User roles updated successfully!');
        
        // Display summary
        $this->line('');
        $this->line('Role Summary:');
        $adminUsers = User::role('Admin')->get();
        $regularUsers = User::role('User')->get();
        
        $this->line("Admins ({$adminUsers->count()}):");
        foreach ($adminUsers as $admin) {
            $this->line("  - {$admin->name} ({$admin->email})");
        }
        
        $this->line("Users ({$regularUsers->count()}):");
        foreach ($regularUsers as $user) {
            $this->line("  - {$user->name} ({$user->email})");
        }
    }
}