<?php

namespace Packages\Crm\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class CrmRoleAccessMiddleware
{
    /**
     * Handle an incoming request.
     * Ensures user is authenticated and has at least one CRM role.
     * Automatically assigns role if user doesn't have one.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $user = auth()->user();

        // Check if Spatie Permission package is installed
        if (!class_exists(\Spatie\Permission\Models\Role::class)) {
            // If package not installed, allow access (roles will be assigned after installation)
            \Log::warning('Spatie Permission package is not installed. Allowing access but RBAC will not work until package is installed.');
            return $next($request);
        }

        // Check if roles table exists (migrations might not have run)
        if (!Schema::hasTable('roles')) {
            // If tables don't exist, allow access but log warning
            \Log::warning('Roles table does not exist. Please run migrations: php artisan migrate');
            return $next($request);
        }

        // Ensure permissions are seeded
        $this->ensurePermissionsSeeded();

        // Check if user has any CRM role (only if tables exist)
        $hasCrmRole = false;
        try {
            if (method_exists($user, 'hasAnyRole') && Schema::hasTable('roles') && Schema::hasTable('model_has_roles')) {
                $hasCrmRole = $user->hasAnyRole(['Admin', 'Manager', 'Executive']);
            }
        } catch (\Exception $e) {
            // If query fails (e.g., tables missing), log and allow access
            \Log::warning('Error checking user roles: ' . $e->getMessage());
            return $next($request);
        }

        if (!$hasCrmRole) {
            // Auto-assign role for existing users who don't have one
            $this->assignRoleToExistingUser($user);
            
            // Check again after assignment
            try {
                if (method_exists($user, 'hasAnyRole') && Schema::hasTable('roles') && Schema::hasTable('model_has_roles')) {
                    $hasCrmRole = $user->hasAnyRole(['Admin', 'Manager', 'Executive']);
                }
            } catch (\Exception $e) {
                \Log::warning('Error checking user roles after assignment: ' . $e->getMessage());
                // Allow access if we can't check roles
                return $next($request);
            }
        }

        if (!$hasCrmRole) {
            abort(403, 'Unauthorized. You do not have a valid CRM role assigned. Please contact administrator.');
        }

        return $next($request);
    }

    /**
     * Ensure permissions and roles are seeded
     */
    protected function ensurePermissionsSeeded()
    {
        // Check if Spatie Permission package is installed
        if (!class_exists(\Spatie\Permission\Models\Role::class)) {
            \Log::warning('Spatie Permission package is not installed. Please run: composer require spatie/laravel-permission');
            return;
        }

        // Check if roles table exists before trying to seed
        if (!Schema::hasTable('roles')) {
            \Log::warning('Roles table does not exist. Please run migrations: php artisan migrate');
            return;
        }

        try {
            $roleClass = \Spatie\Permission\Models\Role::class;
            if (!$roleClass::where('guard_name', 'web')->exists()) {
                $seeder = new \Packages\Crm\Database\Seeders\CrmRolePermissionSeeder();
                $seeder->run();
            }
        } catch (\Exception $e) {
            \Log::error('Failed to seed CRM permissions in middleware: ' . $e->getMessage());
        }
    }

    /**
     * Assign role to existing user who doesn't have one
     */
    protected function assignRoleToExistingUser(User $user)
    {
        // Check if Spatie Permission package is installed
        if (!class_exists(\Spatie\Permission\Models\Role::class)) {
            \Log::warning('Cannot assign role: Spatie Permission package is not installed. Please run: composer require spatie/laravel-permission');
            return;
        }

        // Check if required tables exist
        if (!Schema::hasTable('roles') || !Schema::hasTable('model_has_roles')) {
            \Log::warning('Roles tables do not exist. Please run migrations: php artisan migrate');
            return;
        }

        try {
            $roleClass = \Spatie\Permission\Models\Role::class;
            // Check if this is the first user (oldest user)
            $firstUser = User::orderBy('id', 'asc')->first();
            if ($firstUser && $firstUser->id === $user->id) {
                // Assign Admin role to oldest user
                $role = $roleClass::where('name', 'Admin')->where('guard_name', 'web')->first();
                if ($role) {
                    $user->assignRole($role);
                    \Log::info("Assigned Admin role to user in middleware: {$user->email}");
                }
            } else {
                // Assign Executive role to other existing users
                $role = $roleClass::where('name', 'Executive')->where('guard_name', 'web')->first();
                if ($role) {
                    $user->assignRole($role);
                    \Log::info("Assigned Executive role to user in middleware: {$user->email}");
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to assign role in middleware: ' . $e->getMessage());
        }
    }
}

