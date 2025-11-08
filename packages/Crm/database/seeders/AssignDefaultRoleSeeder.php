<?php

namespace Packages\Crm\database\seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignDefaultRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder assigns the 'Admin' role to all existing users.
     * Run this after CrmRolePermissionSeeder to assign roles to users.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Get the Admin role
        $adminRole = Role::where('name', 'Admin')->where('guard_name', 'web')->first();

        if (!$adminRole) {
            $this->command->error('Admin role not found. Please run CrmRolePermissionSeeder first.');
            return;
        }

        // Assign Admin role to all existing users
        $users = User::all();
        $count = 0;

        foreach ($users as $user) {
            if (!$user->hasRole('Admin')) {
                $user->assignRole('Admin');
                $count++;
            }
        }

        $this->command->info("Assigned 'Admin' role to {$count} user(s).");
    }
}

