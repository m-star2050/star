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
            'role' => 'required|string|in:Manager,Executive',
        ]);
        
        // Prevent assigning Admin role through this interface
        if ($request->input('role') === 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admin role cannot be assigned through this interface.'
            ], 403);
        }

        try {
            // Get old role before changing
            $oldRoles = $user->roles->pluck('name')->toArray();
            $oldRole = $oldRoles[0] ?? null;
            $newRole = $request->input('role');
            
            // Check if this is a promotion or demotion between Executive and Manager
            $isPromotion = ($oldRole === 'Executive' && $newRole === 'Manager');
            $isDemotion = ($oldRole === 'Manager' && $newRole === 'Executive');
            $shouldNotify = $isPromotion || $isDemotion;
            
            // Remove all existing roles
            $user->roles()->detach();

            // Assign new role
            $role = Role::where('name', $request->input('role'))
                        ->where('guard_name', 'web')
                        ->first();

            if ($role) {
                $user->assignRole($role);
                \Log::info("User {$user->email} role updated to {$request->input('role')} by " . auth()->user()->email);

                // If this is a promotion or demotion, create a notification for the user
                if ($shouldNotify) {
                    // Store notification in session for the affected user
                    // We'll use cache with a unique key so the user sees it on their next page load
                    $notificationKey = 'role_change_notification_' . $user->id;
                    $message = $isPromotion 
                        ? "Congratulations! You have been promoted from Executive to Manager. Please refresh the page (F5) to see your updated permissions."
                        : "Your role has been changed from Manager to Executive. Please refresh the page (F5) to see your updated permissions.";
                    
                    // Store in cache for 24 hours (user will see it until they refresh)
                    \Cache::put($notificationKey, [
                        'message' => $message,
                        'old_role' => $oldRole,
                        'new_role' => $newRole,
                        'type' => $isPromotion ? 'promotion' : 'demotion',
                        'created_at' => now()->toDateTimeString()
                    ], now()->addHours(24));
                    
                    \Log::info("Role change notification created for user {$user->email}: {$oldRole} -> {$newRole}");
                }

                return response()->json([
                    'success' => true,
                    'message' => "User role updated to {$request->input('role')} successfully.",
                    'notification_sent' => $shouldNotify
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

    /**
     * Check for role change notifications (API endpoint)
     */
    public function checkNotification()
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'has_notification' => false
            ]);
        }

        $user = auth()->user();
        $notificationKey = 'role_change_notification_' . $user->id;
        $notification = \Cache::get($notificationKey);

        if ($notification) {
            // Return notification and delete it from cache (one-time display)
            \Cache::forget($notificationKey);
            
            return response()->json([
                'success' => true,
                'has_notification' => true,
                'notification' => $notification
            ]);
        }

        return response()->json([
            'success' => true,
            'has_notification' => false
        ]);
    }
}

