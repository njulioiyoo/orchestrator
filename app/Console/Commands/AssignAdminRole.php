<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Permission;

class AssignAdminRole extends Command
{
    protected $signature = 'user:make-admin {username} {--role=Super Admin}';
    protected $description = 'Assign admin role to a specific user';

    public function handle()
    {
        $username = $this->argument('username');
        $roleName = $this->option('role');
        
        // Find user by name or email
        $user = User::where('name', $username)
                   ->orWhere('email', $username)
                   ->first();
        
        if (!$user) {
            $this->error("User '{$username}' not found!");
            return 1;
        }
        
        // Check if role exists, if not create it
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        
        if ($role->wasRecentlyCreated) {
            $this->info("Role '{$roleName}' created.");
            
            // Give all permissions to the role if it's Super Admin
            if ($roleName === 'Super Admin') {
                $permissions = Permission::all();
                $role->givePermissionTo($permissions);
                $this->info("Assigned all permissions to '{$roleName}' role.");
            }
        }
        
        // Assign role to user
        if ($user->hasRole($roleName)) {
            $this->info("User '{$user->name}' already has the '{$roleName}' role.");
        } else {
            $user->assignRole($roleName);
            $this->info("Successfully assigned '{$roleName}' role to user '{$user->name}'.");
        }
        
        // Display user info
        $this->line('');
        $this->line("User Information:");
        $this->line("Name: {$user->name}");
        $this->line("Email: {$user->email}");
        $this->line("Roles: " . implode(', ', $user->getRoleNames()->toArray()));
        $this->line("Permissions: " . implode(', ', $user->getAllPermissions()->pluck('name')->toArray()));
        
        return 0;
    }
}