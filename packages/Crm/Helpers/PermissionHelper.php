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
            \Log::debug('PermissionHelper::isExecutive - User does not have hasRole method', [
                'user_id' => $user->id ?? null
            ]);
            return false;
        }

        try {
            $hasRole = $user->hasRole('Executive');
            \Log::debug('PermissionHelper::isExecutive - Role check result', [
                'user_id' => $user->id ?? null,
                'has_executive_role' => $hasRole,
                'user_roles' => method_exists($user, 'getRoleNames') ? $user->getRoleNames()->toArray() : 'N/A'
            ]);
            return $hasRole;
        } catch (\Exception $e) {
            // If Spatie package not fully set up, log and return false
            \Log::error('PermissionHelper::isExecutive - Exception checking role', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
                \Log::warning('PermissionHelper::filterByRole - No user provided');
                return $query->whereRaw('1 = 0'); // Return empty result if no user
            }

            // Check roles with better error handling
            $isAdmin = false;
            $isManager = false;
            $isExecutive = false;

            try {
                $isAdmin = self::isAdmin($user);
                $isManager = self::isManager($user);
                $isExecutive = self::isExecutive($user);
            } catch (\Exception $e) {
                \Log::error('PermissionHelper::filterByRole - Role check exception: ' . $e->getMessage(), [
                    'user_id' => $user->id ?? null,
                    'trace' => $e->getTraceAsString()
                ]);
            }

            // Admin sees everything
            if ($isAdmin) {
                return $query;
            }

            // Manager sees everything (team data)
            if ($isManager) {
                return $query;
            }

            // Executive sees only assigned records
            if ($isExecutive) {
                $userId = $user->id;
                
                \Log::debug('PermissionHelper::filterByRole - Filtering for Executive user', [
                    'user_id' => $userId,
                    'assigned_field' => $assignedField,
                    'owner_field' => $ownerField
                ]);
                
                return $query->where(function($q) use ($userId, $assignedField, $ownerField) {
                    $hasConditions = false;
                    
                    // Check assigned field (only if provided)
                    if ($assignedField) {
                        $q->where($assignedField, $userId);
                        $hasConditions = true;
                    }
                    
                    // Check owner field (only if provided and different from assigned field)
                    // Important: Pass null for ownerField if the table doesn't have this column
                    if ($ownerField && $ownerField !== $assignedField) {
                        if ($hasConditions) {
                            $q->orWhere($ownerField, $userId);
                        } else {
                            $q->where($ownerField, $userId);
                            $hasConditions = true;
                        }
                    }
                    
                    // For files, also check uploaded_by (only if no other fields specified)
                    if (!$hasConditions && $assignedField === null && $ownerField === null) {
                        $q->where('uploaded_by', $userId);
                        $hasConditions = true;
                    }
                    
                    // If no conditions were added, return empty result for safety
                    if (!$hasConditions) {
                        \Log::warning('PermissionHelper::filterByRole - No conditions added for Executive user', [
                            'user_id' => $userId,
                            'assigned_field' => $assignedField,
                            'owner_field' => $ownerField
                        ]);
                        $q->whereRaw('1 = 0');
                    }
                });
            }

            // Log if user doesn't match any role
            \Log::warning('PermissionHelper::filterByRole - User does not match any role, denying access', [
                'user_id' => $user->id ?? null,
                'is_admin' => $isAdmin,
                'is_manager' => $isManager,
                'is_executive' => $isExecutive
            ]);

            // Default: no access
            return $query->whereRaw('1 = 0');
        } catch (\Exception $e) {
            // Log error and return empty result to prevent crashes
            \Log::error('PermissionHelper::filterByRole error: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
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

    /**
     * Get users for dropdown/selection, excluding admins for non-admin users
     * 
     * @param \App\Models\User|null $currentUser The current authenticated user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getUsersForSelection($currentUser = null)
    {
        $currentUser = $currentUser ?? Auth::user();
        
        if (!$currentUser) {
            return collect([]);
        }

        try {
            $query = \App\Models\User::select('id', 'name', 'email')->orderBy('name');
            
            // Only admins can see admin users in dropdowns
            // Managers and executives should not see admin users
            if (!self::isAdmin($currentUser)) {
                // Check if Spatie package is installed and roles table exists
                if (class_exists(\Spatie\Permission\Models\Role::class) && \Illuminate\Support\Facades\Schema::hasTable('roles')) {
                    // Filter out users with Admin role
                    $query->whereDoesntHave('roles', function($q) {
                        $q->where('name', 'Admin');
                    });
                } else {
                    // If Spatie is not installed, we can't filter by roles
                    // In this case, return all users (fallback behavior)
                    \Log::debug('PermissionHelper::getUsersForSelection - Spatie package not installed, returning all users');
                }
            }
            
            return $query->get();
        } catch (\Exception $e) {
            \Log::error('PermissionHelper::getUsersForSelection error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            // On error, return all users as fallback to prevent breaking the application
            try {
                return \App\Models\User::select('id', 'name', 'email')->orderBy('name')->get();
            } catch (\Exception $fallbackError) {
                \Log::error('PermissionHelper::getUsersForSelection fallback error: ' . $fallbackError->getMessage());
                return collect([]);
            }
        }
    }
}

