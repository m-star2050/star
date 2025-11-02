<?php

namespace Packages\Crm\Helpers;

class PermissionHelper
{
    /**
     * Check if user has permission
     * 
     * This is a simplified permission system.
     * For full implementation, install and configure Spatie Laravel Permission.
     * 
     * Roles:
     * - Admin (role_id = 1): Full access to all CRM features
     * - Manager (role_id = 2): Can view and manage team data
     * - Executive (role_id = 3): Can only access assigned records
     */
    public static function can($action, $userId = null)
    {
        $userId = $userId ?? auth()->id();
        
        // If no user, deny
        if (!$userId) {
            return false;
        }

        // Get user role (you can customize this based on your users table)
        // For now, assume all authenticated users have permission
        // In production, check: $user->role_id or use Spatie Permission
        
        return true; // Allow all authenticated users for now
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin($userId = null)
    {
        $userId = $userId ?? auth()->id();
        
        // In production: return auth()->user()->hasRole('admin');
        // For now, return true to allow development
        
        return true;
    }

    /**
     * Check if user is manager
     */
    public static function isManager($userId = null)
    {
        $userId = $userId ?? auth()->id();
        
        // In production: return auth()->user()->hasRole('manager');
        
        return true;
    }

    /**
     * Check if user can access record (for Executive role)
     */
    public static function canAccessRecord($record, $userId = null)
    {
        $userId = $userId ?? auth()->id();
        
        // Admin can access everything
        if (self::isAdmin($userId)) {
            return true;
        }

        // Check if record is assigned to user
        if (isset($record->assigned_user_id) && $record->assigned_user_id == $userId) {
            return true;
        }

        if (isset($record->owner_user_id) && $record->owner_user_id == $userId) {
            return true;
        }

        // Manager can access team records (implement team logic here)
        if (self::isManager($userId)) {
            return true;
        }

        return false;
    }

    /**
     * Filter query based on user role
     */
    public static function filterByRole($query, $userId = null)
    {
        $userId = $userId ?? auth()->id();

        // Admin sees everything
        if (self::isAdmin($userId)) {
            return $query;
        }

        // Executive sees only assigned records
        // Uncomment and customize based on your needs:
        // return $query->where(function($q) use ($userId) {
        //     $q->where('assigned_user_id', $userId)
        //       ->orWhere('owner_user_id', $userId);
        // });

        return $query;
    }
}

