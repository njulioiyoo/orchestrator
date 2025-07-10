<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Master Menu
        $masterMenu = Menu::create([
            'name' => 'master',
            'label' => 'Master',
            'icon' => 'fa fa-cogs',
            'url' => null,
            'route' => null,
            'parent_id' => null,
            'sort_order' => 1,
            'is_active' => true,
            'permissions' => [
                ['type' => 'role', 'name' => 'Admin']
            ]
        ]);

        // System Menu (child of Master)
        $systemMenu = Menu::create([
            'name' => 'system',
            'label' => 'System',
            'icon' => 'fa fa-database',
            'url' => null,
            'route' => null,
            'parent_id' => $masterMenu->id,
            'sort_order' => 1,
            'is_active' => true,
            'permissions' => [
                ['type' => 'role', 'name' => 'Admin']
            ]
        ]);

        // Users Menu (child of System)
        Menu::create([
            'name' => 'users',
            'label' => 'Users',
            'icon' => 'fa fa-users',
            'url' => '/system/users',
            'route' => null,
            'parent_id' => $systemMenu->id,
            'sort_order' => 1,
            'is_active' => true,
            'permissions' => [
                ['type' => 'role', 'name' => 'Admin']
            ]
        ]);

        // Roles Menu (child of System)
        Menu::create([
            'name' => 'roles',
            'label' => 'Roles',
            'icon' => 'fa fa-user-circle',
            'url' => '/system/roles',
            'route' => null,
            'parent_id' => $systemMenu->id,
            'sort_order' => 2,
            'is_active' => true,
            'permissions' => [
                ['type' => 'role', 'name' => 'Admin']
            ]
        ]);

        // Permissions Menu (child of System)
        Menu::create([
            'name' => 'permissions',
            'label' => 'Permissions',
            'icon' => 'fa fa-shield',
            'url' => '/system/permissions',
            'route' => null,
            'parent_id' => $systemMenu->id,
            'sort_order' => 3,
            'is_active' => true,
            'permissions' => [
                ['type' => 'role', 'name' => 'Admin']
            ]
        ]);

        // Audit Logs Menu (child of System)
        Menu::create([
            'name' => 'audits',
            'label' => 'Audit Logs',
            'icon' => 'fa fa-history',
            'url' => '/system/audits',
            'route' => null,
            'parent_id' => $systemMenu->id,
            'sort_order' => 4,
            'is_active' => true,
            'permissions' => [
                ['type' => 'role', 'name' => 'Admin']
            ]
        ]);

        // Menus Management (child of System)
        Menu::create([
            'name' => 'menus',
            'label' => 'Menus',
            'icon' => 'fa fa-bars',
            'url' => '/system/menus',
            'route' => null,
            'parent_id' => $systemMenu->id,
            'sort_order' => 5,
            'is_active' => true,
            'permissions' => [
                ['type' => 'role', 'name' => 'Admin']
            ]
        ]);
    }
}
