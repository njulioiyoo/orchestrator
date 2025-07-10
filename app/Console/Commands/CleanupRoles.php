<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Permission;

class CleanupRoles extends Command
{
    protected $signature = 'roles:cleanup';
    protected $description = 'Clean up roles table to only have Admin and User roles';

    public function handle()
    {
        $this->info('Cleaning up roles table...');
        
        // Get all users and their current roles
        $users = User::with('roles')->get();
        $this->info("Found {$users->count()} users");
        
        // Show current roles
        $currentRoles = Role::all();
        $this->info("Current roles in database:");
        foreach ($currentRoles as $role) {
            $this->line("  - {$role->name} (ID: {$role->id})");
        }
        
        // Remove all existing roles and their relationships
        $this->info('Removing all existing roles...');
        Role::query()->delete();
        
        // Clear all pivot tables
        \DB::table('model_has_roles')->truncate();
        \DB::table('role_has_permissions')->truncate();
        
        $this->info('Creating clean Admin and User roles...');
        
        // Create only Admin and User roles
        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $userRole = Role::create(['name' => 'User', 'guard_name' => 'web']);
        
        // Assign permissions to Admin role
        $permissions = Permission::all();
        if ($permissions->count() > 0) {
            $adminRole->givePermissionTo($permissions);
            $this->info("Assigned {$permissions->count()} permissions to Admin role");
        }
        
        // Assign only dashboard permission to User role
        $dashboardPermission = Permission::where('name', 'view dashboard')->first();
        if ($dashboardPermission) {
            $userRole->givePermissionTo($dashboardPermission);
            $this->info("Assigned dashboard permission to User role");
        }
        
        // Reassign roles to users
        foreach ($users as $user) {
            if ($user->name === 'njulioiyoo') {
                $user->assignRole('Admin');
                $this->info("Assigned Admin role to {$user->name}");
            } else {
                $user->assignRole('User');
                $this->info("Assigned User role to {$user->name}");
            }
        }
        
        // Show final results
        $this->info('');
        $this->info('Final roles in database:');
        $finalRoles = Role::all();
        foreach ($finalRoles as $role) {
            $userCount = User::role($role->name)->count();
            $this->line("  - {$role->name} (ID: {$role->id}) - {$userCount} users");
        }
        
        $this->info('');
        $this->info('User role assignments:');
        foreach ($users as $user) {
            $user->refresh();
            $roles = $user->getRoleNames()->toArray();
            $this->line("  - {$user->name}: " . implode(', ', $roles));
        }
        
        $this->info('');
        $this->info('Roles cleanup completed successfully!');
    }
}