<?php

namespace Packages\Crm\Helpers;

use Illuminate\Support\Facades\Auth;

class PermissionHelper
{
    /**
     * Check if user has permission
     * Uses Spatie Laravel Permission package
     */
    public static function can($permission, $user = null)
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return false;
        }

        return $user->can($permission);
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin($user = null)
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return false;
        }

        // Check if user has the hasRole method (Spatie package installed)
        if (!method_exists($user, 'hasRole')) {
            return false;
        }

        try {
            return $user->hasRole('Admin');
        } catch (\Exception $e) {
            // If Spatie package not fully set up, return false
            return false;
        }
    }

    /**
     * Check if user is manager
     */
    public static function isManager($user = null)
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return false;
        }

        // Check if user has the hasRole method (Spatie package installed)
        if (!method_exists($user, 'hasRole')) {
            return false;
        }

        try {
            return $user->hasRole('Manager');
        } catch (\Exception $e) {
            // If Spatie package not fully set up, return false
            return false;
        }
    }

    /**
     * Check if user is executive
     */
    public static function isExecutive($user = null)
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return false;
        }

        // Check if user has the hasRole method (Spatie package installed)
        if (!method_exists($user, 'hasRole')) {
            return false;
        }

        try {
            return $user->hasRole('Executive');
        } catch (\Exception $e) {
            // If Spatie package not fully set up, return false
            return false;
        }
    }

    /**
     * Check if user can access record (for Executive role)
     */
    public static function canAccessRecord($record, $user = null)
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return false;
        }

        // Admin can access everything
        if (self::isAdmin($user)) {
            return true;
        }

        // Manager can access team records (all records)
        if (self::isManager($user)) {
            return true;
        }

        // Executive can only access assigned records
        if (self::isExecutive($user)) {
            $userId = $user->id;
            
            // Check if record is assigned to user
            if (isset($record->assigned_user_id) && $record->assigned_user_id == $userId) {
                return true;
            }

            if (isset($record->owner_user_id) && $record->owner_user_id == $userId) {
                return true;
            }

            // For files, check uploaded_by
            if (isset($record->uploaded_by) && $record->uploaded_by == $userId) {
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Filter query based on user role
     */
    public static function filterByRole($query, $user = null, $assignedField = 'assigned_user_id', $ownerField = 'owner_user_id')
    {
        try {
            $user = $user ?? Auth::user();

            if (!$user) {
                return $query->whereRaw('1 = 0'); // Return empty result if no user
            }

            // Admin sees everything
            if (self::isAdmin($user)) {
                return $query;
            }

            // Manager sees everything (team data)
            if (self::isManager($user)) {
                return $query;
            }

            // Executive sees only assigned records
            if (self::isExecutive($user)) {
                $userId = $user->id;
                return $query->where(function($q) use ($userId, $assignedField, $ownerField) {
                    if ($assignedField) {
                        $q->where($assignedField, $userId);
                    }
                    if ($ownerField && $ownerField !== $assignedField) {
                        $q->orWhere($ownerField, $userId);
                    }
                    // For files, also check uploaded_by
                    if ($assignedField === null && $ownerField === null) {
                        $q->orWhere('uploaded_by', $userId);
                    }
                });
            }

            // Default: no access
            return $query->whereRaw('1 = 0');
        } catch (\Exception $e) {
            // Log error and return empty result to prevent crashes
            \Log::error('PermissionHelper::filterByRole error: ' . $e->getMessage());
            return $query->whereRaw('1 = 0');
        }
    }

    /**
     * Check if user can delete records
     */
    public static function canDelete($modelType, $user = null)
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return false;
        }

        $permissionMap = [
            'contact' => 'delete contacts',
            'lead' => 'delete leads',
            'task' => 'delete tasks',
            'pipeline' => 'delete pipeline',
            'file' => 'delete files',
        ];

        $permission = $permissionMap[strtolower($modelType)] ?? null;
        
        if (!$permission) {
            return false;
        }

        return $user->can($permission);
    }

    /**
     * Check if user can export data
     */
    public static function canExport($modelType, $user = null)
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return false;
        }

        $permissionMap = [
            'contact' => 'export contacts',
            'lead' => 'export leads',
            'task' => 'export tasks',
            'pipeline' => 'export pipeline',
            'report' => 'export reports',
        ];

        $permission = $permissionMap[strtolower($modelType)] ?? null;
        
        if (!$permission) {
            return false;
        }

        return $user->can($permission);
    }
}

