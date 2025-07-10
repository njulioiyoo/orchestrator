<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class TestPermissions extends Command
{
    protected $signature = 'test:permissions';
    protected $description = 'Test permission system';

    public function handle()
    {
        $this->info('Testing permission system...');
        
        // Check if permissions exist
        $permissions = Permission::all();
        $this->info("Total permissions: " . $permissions->count());
        
        // Check if roles exist
        $roles = Role::all();
        $this->info("Total roles: " . $roles->count());
        
        // Check if users exist
        $users = User::all();
        $this->info("Total users: " . $users->count());
        
        if ($users->count() > 0) {
            $user = $users->first();
            $this->info("First user: " . $user->name);
            $this->info("User permissions: " . implode(', ', $user->getAllPermissions()->pluck('name')->toArray()));
            $this->info("User roles: " . implode(', ', $user->getRoleNames()->toArray()));
        }
        
        $this->info('Permission system test completed!');
    }
}