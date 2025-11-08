<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Packages\Crm\database\seeders\CrmRolePermissionSeeder;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        // If already authenticated, redirect to files page
        if (Auth::check()) {
            return redirect()->route('crm.files.index');
        }
        
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Check if users table exists
        if (!Schema::hasTable('users')) {
            return back()->withErrors([
                'email' => 'Database not set up. Please run migrations: php artisan migrate',
            ])->withInput();
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            // Ensure permissions are seeded
            $this->ensurePermissionsSeeded();
            
            // Ensure user has a role (for existing users who registered before RBAC)
            $user = Auth::user();
            if ($user && !$user->roles()->exists()) {
                // Check if this is the first user (oldest user)
                $firstUser = User::orderBy('id', 'asc')->first();
                if ($firstUser && $firstUser->id === $user->id) {
                    // Assign Admin role to oldest user
                    $this->assignRoleToUser($user, true);
                } else {
                    // Assign Executive role to other existing users
                    $this->assignRoleToUser($user, false);
                }
            }
            
            return redirect()->intended(route('crm.files.index'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show registration form
     */
    public function showRegisterForm()
    {
        // If already authenticated, redirect to files page
        if (Auth::check()) {
            return redirect()->route('crm.files.index');
        }
        
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        // Check if users table exists
        if (!Schema::hasTable('users')) {
            return back()->withErrors([
                'email' => 'Database not set up. Please run migrations: php artisan migrate',
            ])->withInput();
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Ensure permissions and roles are seeded before creating user
        $this->ensurePermissionsSeeded();

        // Check if this is the first user (before creating)
        $isFirstUser = User::count() === 0;

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ];
        
        // Add role_id only if column exists in users table
        if (Schema::hasColumn('users', 'role_id')) {
            $userData['role_id'] = 1; // Default role_id for new users
        }
        
        $user = User::create($userData);

        // Assign role automatically (first user gets Admin, others get Executive)
        $this->assignRoleToUser($user, $isFirstUser);

        Auth::login($user);

        return redirect()->route('crm.files.index')->with('success', 'Registration successful! Welcome!');
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

        try {
            $roleClass = \Spatie\Permission\Models\Role::class;
            // Check if roles exist, if not, seed them
            if (!$roleClass::where('guard_name', 'web')->exists()) {
                $seeder = new CrmRolePermissionSeeder();
                $seeder->run();
            }
        } catch (\Exception $e) {
            // If seeding fails, log but don't block registration
            \Log::error('Failed to seed CRM permissions: ' . $e->getMessage());
        }
    }

    /**
     * Assign role to newly registered user
     * First user gets Admin role, others get Executive by default
     */
    protected function assignRoleToUser(User $user, bool $isFirstUser = false)
    {
        // Check if Spatie Permission package is installed
        if (!class_exists(\Spatie\Permission\Models\Role::class)) {
            \Log::warning('Cannot assign role: Spatie Permission package is not installed. Please run: composer require spatie/laravel-permission');
            return;
        }

        try {
            $roleClass = \Spatie\Permission\Models\Role::class;
            if ($isFirstUser) {
                // First user is Admin - gets full access
                $role = $roleClass::where('name', 'Admin')->where('guard_name', 'web')->first();
                if ($role) {
                    $user->assignRole($role);
                    \Log::info("Assigned Admin role to first user: {$user->email}");
                }
            } else {
                // Default role for new users is Executive (limited access)
                $role = $roleClass::where('name', 'Executive')->where('guard_name', 'web')->first();
                if ($role) {
                    $user->assignRole($role);
                    \Log::info("Assigned Executive role to user: {$user->email}");
                } else {
                    // Fallback: try to assign Admin if Executive doesn't exist
                    $adminRole = $roleClass::where('name', 'Admin')->where('guard_name', 'web')->first();
                    if ($adminRole) {
                        $user->assignRole($adminRole);
                        \Log::warning("Executive role not found, assigned Admin to user: {$user->email}");
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to assign role to user: ' . $e->getMessage());
        }
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

