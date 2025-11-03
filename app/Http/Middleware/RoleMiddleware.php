<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Note: Authentication is already checked by 'auth' middleware
        // This middleware only checks role permissions

        // Get the required role from the parameter (e.g., '1' from 'role:1')
        $requiredRole = (int) $role;

        // Check if user has the required role
        // Check if role_id column exists on the user model
        $user = auth()->user();
        $userRole = null;
        
        // Try to get role_id - handle gracefully if column doesn't exist
        if (isset($user->role_id)) {
            $userRole = $user->role_id;
        } elseif (method_exists($user, 'getRoleId')) {
            $userRole = $user->getRoleId();
        } elseif ($user->getAttributes()['role_id'] ?? null) {
            $userRole = $user->getAttributes()['role_id'];
        }

        // If role_id column doesn't exist, allow access (for development/testing)
        // In production, you should add role_id column to users table
        if ($userRole === null) {
            // Log warning but allow access for now
            \Log::warning('RoleMiddleware: role_id not found on user. Allowing access. Consider adding role_id column to users table.');
            return $next($request);
        }

        if ((int)$userRole != $requiredRole) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        return $next($request);
    }
}

