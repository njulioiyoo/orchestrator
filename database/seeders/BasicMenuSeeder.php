<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Tenant;
use Spatie\Permission\Models\Permission;

class BasicMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get default tenant
        $defaultTenant = Tenant::where('slug', 'default')->first();
        
        if (!$defaultTenant) {
            $this->command->error('Default tenant not found. Please run SuperAdminSeeder first.');
            return;
        }

        // Create dashboard permission if not exists
        Permission::firstOrCreate([
            'name' => 'view dashboard',
            'tenant_id' => $defaultTenant->id,
        ], [
            'guard_name' => 'web',
        ]);

        // Create Dashboard menu if not exists
        $dashboardMenu = Menu::firstOrCreate([
            'name' => 'dashboard',
            'tenant_id' => $defaultTenant->id,
        ], [
            'label' => 'Dashboard',
            'icon' => 'fa fa-dashboard',
            'url' => '/dashboard',
            'route' => 'dashboard',
            'parent_id' => null,
            'sort_order' => 1,
            'is_active' => true,
            'permissions' => [
                ['type' => 'permission', 'name' => 'view dashboard']
            ]
        ]);

        // Update System menu to be first in order
        $systemMenu = Menu::where('name', 'system')->where('tenant_id', $defaultTenant->id)->first();
        if ($systemMenu) {
            $systemMenu->sort_order = 2;
            $systemMenu->save();
        }

        // Update Tenant Management menu to be third in order
        $tenantMenu = Menu::where('name', 'tenant-management')->where('tenant_id', $defaultTenant->id)->first();
        if ($tenantMenu) {
            $tenantMenu->sort_order = 3;
            $tenantMenu->save();
        }

        $this->command->info('Basic menu structure created successfully!');
        $this->command->info('Menu order: Dashboard -> System -> Tenant Management');
    }
}