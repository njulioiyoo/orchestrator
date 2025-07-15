<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class FixMenuPermissionFormatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all menus
        $menus = Menu::all();
        
        foreach ($menus as $menu) {
            if ($menu->permissions) {
                $updatedPermissions = [];
                
                foreach ($menu->permissions as $permission) {
                    // If it's already in the correct format, keep it
                    if (is_array($permission) && isset($permission['type']) && isset($permission['name'])) {
                        $updatedPermissions[] = $permission;
                    } else {
                        // Convert string permission to object format
                        $updatedPermissions[] = [
                            'type' => 'permission',
                            'name' => $permission
                        ];
                    }
                }
                
                $menu->permissions = $updatedPermissions;
                $menu->save();
            }
        }
        
        $this->command->info('Menu permissions format fixed successfully!');
        
        // Show updated menus
        $this->command->info('Updated menu permissions:');
        $menus = Menu::all();
        foreach ($menus as $menu) {
            $this->command->line($menu->label . ': ' . json_encode($menu->permissions));
        }
    }
}