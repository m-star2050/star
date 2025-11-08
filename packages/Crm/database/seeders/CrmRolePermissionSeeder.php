<?php

namespace Packages\Crm\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CrmRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Reset cached roles and permissions
            if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            }

            // Check if required tables exist
            if (!Schema::hasTable('roles') || !Schema::hasTable('permissions')) {
                \Log::warning('Spatie Permission tables do not exist. Please run migrations: php artisan migrate');
                return;
            }

            // Create permissions
            $permissions = [
                // Contacts
                'view contacts',
                'create contacts',
                'edit contacts',
                'delete contacts',
                'export contacts',
                
                // Leads
                'view leads',
                'create leads',
                'edit leads',
                'delete leads',
                'export leads',
                
                // Tasks
                'view tasks',
                'create tasks',
                'edit tasks',
                'delete tasks',
                'export tasks',
                
                // Pipeline/Deals
                'view pipeline',
                'create pipeline',
                'edit pipeline',
                'delete pipeline',
                'export pipeline',
                
                // Reports
                'view reports',
                'export reports',
                
                // Files
                'view files',
                'upload files',
                'delete files',
                
                // Admin permissions
                'manage all data',
                'manage team data',
            ];

            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            }

            // Create roles
            $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
            $managerRole = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
            $executiveRole = Role::firstOrCreate(['name' => 'Executive', 'guard_name' => 'web']);

            // Admin: Full access to all permissions
            $adminRole->givePermissionTo(Permission::all());

            // Manager: View and manage team data (no delete/export restrictions for team data)
            $managerPermissions = [
                'view contacts', 'create contacts', 'edit contacts', 'delete contacts', 'export contacts',
                'view leads', 'create leads', 'edit leads', 'delete leads', 'export leads',
                'view tasks', 'create tasks', 'edit tasks', 'delete tasks', 'export tasks',
                'view pipeline', 'create pipeline', 'edit pipeline', 'delete pipeline', 'export pipeline',
                'view reports', 'export reports',
                'view files', 'upload files', 'delete files',
                'manage team data',
            ];
            $managerRole->givePermissionTo($managerPermissions);

            // Executive: Access only assigned records (view, create, edit - no delete/export)
            $executivePermissions = [
                'view contacts', 'create contacts', 'edit contacts',
                'view leads', 'create leads', 'edit leads',
                'view tasks', 'create tasks', 'edit tasks',
                'view pipeline', 'create pipeline', 'edit pipeline',
                'view reports',
                'view files', 'upload files',
            ];
            $executiveRole->givePermissionTo($executivePermissions);

            // Also insert into crm_roles table for reference (if table exists)
            if (Schema::hasTable('crm_roles')) {
                DB::table('crm_roles')->insertOrIgnore([
                    ['name' => 'Admin', 'description' => 'Full access to all CRM features', 'created_at' => now(), 'updated_at' => now()],
                    ['name' => 'Manager', 'description' => 'Can view and manage team data', 'created_at' => now(), 'updated_at' => now()],
                    ['name' => 'Executive', 'description' => 'Can only access assigned records', 'created_at' => now(), 'updated_at' => now()],
                ]);
            }

            // Only output message if running from command line
            if ($this->command) {
                $this->command->info('CRM Roles and Permissions seeded successfully!');
            } else {
                \Log::info('CRM Roles and Permissions seeded successfully!');
            }
        } catch (\Exception $e) {
            // Log error but don't throw (allows application to continue)
            \Log::error('Error seeding CRM roles and permissions: ' . $e->getMessage());
            if ($this->command) {
                $this->command->error('Error seeding CRM roles and permissions: ' . $e->getMessage());
            }
        }
    }
}

