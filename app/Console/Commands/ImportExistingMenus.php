<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Menu;

class ImportExistingMenus extends Command
{
    protected $signature = 'menu:import-existing';
    protected $description = 'Import existing static menu items to database';

    public function handle()
    {
        $this->info('Importing existing menu items...');

        try {
            // Create Master Menu
            $masterMenu = Menu::firstOrCreate([
                'name' => 'master'
            ], [
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

            // Create System Menu (child of Master)
            $systemMenu = Menu::firstOrCreate([
                'name' => 'system',
                'parent_id' => $masterMenu->id
            ], [
                'label' => 'System',
                'icon' => 'fa fa-database',
                'url' => null,
                'route' => null,
                'sort_order' => 1,
                'is_active' => true,
                'permissions' => [
                    ['type' => 'role', 'name' => 'Admin']
                ]
            ]);

            // Create system sub-menus
            $systemMenus = [
                [
                    'name' => 'users',
                    'label' => 'Users',
                    'icon' => 'fa fa-users',
                    'url' => '/system/users',
                    'sort_order' => 1,
                ],
                [
                    'name' => 'roles',
                    'label' => 'Roles',
                    'icon' => 'fa fa-user-circle',
                    'url' => '/system/roles',
                    'sort_order' => 2,
                ],
                [
                    'name' => 'permissions',
                    'label' => 'Permissions',
                    'icon' => 'fa fa-shield',
                    'url' => '/system/permissions',
                    'sort_order' => 3,
                ],
                [
                    'name' => 'audits',
                    'label' => 'Audit Logs',
                    'icon' => 'fa fa-history',
                    'url' => '/system/audits',
                    'sort_order' => 4,
                ],
                [
                    'name' => 'menus',
                    'label' => 'Menus',
                    'icon' => 'fa fa-bars',
                    'url' => '/system/menus',
                    'sort_order' => 5,
                ]
            ];

            foreach ($systemMenus as $menuData) {
                Menu::firstOrCreate([
                    'name' => $menuData['name'],
                    'parent_id' => $systemMenu->id
                ], [
                    'label' => $menuData['label'],
                    'icon' => $menuData['icon'],
                    'url' => $menuData['url'],
                    'route' => null,
                    'sort_order' => $menuData['sort_order'],
                    'is_active' => true,
                    'permissions' => [
                        ['type' => 'role', 'name' => 'Admin']
                    ]
                ]);
            }

            $this->info('Successfully imported existing menu items!');
            $this->info('Menu structure:');
            $this->info('- Master');
            $this->info('  - System');
            $this->info('    - Users');
            $this->info('    - Roles');
            $this->info('    - Permissions');
            $this->info('    - Audit Logs');
            $this->info('    - Menus');

        } catch (\Exception $e) {
            $this->error('Error importing menus: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
