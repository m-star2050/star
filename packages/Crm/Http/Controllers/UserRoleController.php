<?php

namespace Packages\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Packages\Crm\Helpers\PermissionHelper;

class UserRoleController extends Controller
{
    /**
     * Display a list of users with their roles
     */
    public function index()
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized. Please login to access this page.');
        }
        
        // Allow access for Admin role OR first user (by ID)
        $user = auth()->user();
        $firstUser = \App\Models\User::orderBy('id', 'asc')->first();
        $isFirstUser = $firstUser && $firstUser->id === $user->id;
        $isAdmin = PermissionHelper::isAdmin();
        
        if (!$isAdmin && !$isFirstUser) {
            abort(403, 'Unauthorized. Only administrators can manage user roles.');
        }

        // Check if Spatie package is installed
        if (!class_exists(\Spatie\Permission\Models\Role::class) || !Schema::hasTable('roles')) {
            return view('crm::user-roles.index', [
                'users' => User::all(),
                'roles' => collect([]),
                'packageInstalled' => false,
            ]);
        }

        $users = User::with('roles')->get();
        $roles = Role::where('guard_name', 'web')->get();

        return view('crm::user-roles.index', [
            'users' => $users,
            'roles' => $roles,
            'packageInstalled' => true,
        ]);
    }

    /**
     * Update user role
     */
    public function update(Request $request, User $user)
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized. Please login to access this page.');
        }
        
        // Allow access for Admin role OR first user (by ID)
        $currentUser = auth()->user();
        $firstUser = \App\Models\User::orderBy('id', 'asc')->first();
        $isFirstUser = $firstUser && $firstUser->id === $currentUser->id;
        $isAdmin = PermissionHelper::isAdmin();
        
        if (!$isAdmin && !$isFirstUser) {
            abort(403, 'Unauthorized. Only administrators can manage user roles.');
        }

        // Check if Spatie package is installed
        if (!class_exists(\Spatie\Permission\Models\Role::class) || !Schema::hasTable('roles')) {
            return response()->json([
                'success' => false,
                'message' => 'Spatie Permission package is not installed. Please install it first.'
            ], 400);
        }

        $request->validate([
            'role' => 'required|string|in:Admin,Manager,Executive',
        ]);

        try {
            // Remove all existing roles
            $user->roles()->detach();

            // Assign new role
            $role = Role::where('name', $request->input('role'))
                        ->where('guard_name', 'web')
                        ->first();

            if ($role) {
                $user->assignRole($role);
                \Log::info("User {$user->email} role updated to {$request->input('role')} by " . auth()->user()->email);

                return response()->json([
                    'success' => true,
                    'message' => "User role updated to {$request->input('role')} successfully."
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Role not found.'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Error updating user role: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating user role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user role information (API endpoint)
     */
    public function show(User $user)
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized. Please login to access this page.');
        }
        
        // Allow access for Admin role OR first user (by ID)
        $currentUser = auth()->user();
        $firstUser = \App\Models\User::orderBy('id', 'asc')->first();
        $isFirstUser = $firstUser && $firstUser->id === $currentUser->id;
        $isAdmin = PermissionHelper::isAdmin();
        
        if (!$isAdmin && !$isFirstUser) {
            abort(403, 'Unauthorized.');
        }

        // Check if Spatie package is installed
        if (!class_exists(\Spatie\Permission\Models\Role::class) || !Schema::hasTable('roles')) {
            return response()->json([
                'success' => false,
                'message' => 'Spatie Permission package is not installed.'
            ], 400);
        }

        $userRoles = $user->roles->pluck('name')->toArray();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $userRoles,
                'primary_role' => $userRoles[0] ?? null,
            ]
        ]);
    }
}

