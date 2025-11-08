<?php

namespace Packages\Crm\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

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
     * Check if user can access record
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

        // Managers and Executives can only access their own records (where user_id matches)
        $userId = $user->id;
        
        // Check if record has user_id and it matches the current user
        if (isset($record->user_id) && $record->user_id == $userId) {
            return true;
        }

        // Fallback: Check old fields for backward compatibility
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

    /**
     * Filter query based on user role
     * Admins see all records, Managers and Executives see only their own records (user_id = their ID)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User|null $user
     * @param string|null $assignedField Deprecated - kept for backward compatibility
     * @param string|null $ownerField Deprecated - kept for backward compatibility
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function filterByRole($query, $user = null, $assignedField = null, $ownerField = null)
    {
        try {
            $user = $user ?? Auth::user();

            if (!$user) {
                \Log::warning('PermissionHelper::filterByRole - No user provided');
                return $query->whereRaw('1 = 0'); // Return empty result if no user
            }

            // Check if user is admin
            $isAdmin = false;
            try {
                $isAdmin = self::isAdmin($user);
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

            // Managers and Executives see only their own records (where user_id matches)
            $userId = $user->id;
            
            // Get the table name from the query
            $tableName = $query->getModel()->getTable();
            
            return $query->where(function($q) use ($userId, $tableName) {
                // Primary: Check user_id field (new standard)
                $q->where('user_id', $userId);
                
                // Fallback: Check old fields for backward compatibility if user_id is null
                // This handles cases where records haven't been migrated yet
                $q->orWhere(function($subQ) use ($userId, $tableName) {
                    $subQ->whereNull('user_id')
                         ->where(function($fallbackQ) use ($userId, $tableName) {
                             // Check if assigned_user_id exists and matches
                             if (Schema::hasColumn($tableName, 'assigned_user_id')) {
                                 $fallbackQ->where('assigned_user_id', $userId);
                             }
                             // Check if owner_user_id exists and matches
                             if (Schema::hasColumn($tableName, 'owner_user_id')) {
                                 $fallbackQ->orWhere('owner_user_id', $userId);
                             }
                             // Check if uploaded_by exists and matches (for files)
                             if (Schema::hasColumn($tableName, 'uploaded_by')) {
                                 $fallbackQ->orWhere('uploaded_by', $userId);
                             }
                         });
                });
            });
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
     * Filter query by user_id - simplified version for new standard
     * Admins see all, others see only their own records
     * Includes fallback to old fields for backward compatibility
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User|null $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function filterByUserId($query, $user = null)
    {
        try {
            $user = $user ?? Auth::user();

            if (!$user) {
                return $query->whereRaw('1 = 0');
            }

            // Admin sees everything
            if (self::isAdmin($user)) {
                return $query;
            }

            // Others see only their own records
            $userId = $user->id;
            
            // Get the table name from the query
            $tableName = $query->getModel()->getTable();
            
            // Check if user_id column exists
            $hasUserIdColumn = Schema::hasColumn($tableName, 'user_id');
            
            if ($hasUserIdColumn) {
                // Use user_id with fallback to old fields for backward compatibility
                return $query->where(function($q) use ($userId, $tableName) {
                    // Primary: Check user_id field (new standard)
                    $q->where('user_id', $userId);
                    
                    // Fallback: Check old fields for backward compatibility if user_id is null
                    // This handles cases where records haven't been migrated yet
                    $q->orWhere(function($subQ) use ($userId, $tableName) {
                        $subQ->whereNull('user_id')
                             ->where(function($fallbackQ) use ($userId, $tableName) {
                                 // Check if assigned_user_id exists and matches
                                 if (Schema::hasColumn($tableName, 'assigned_user_id')) {
                                     $fallbackQ->where('assigned_user_id', $userId);
                                 }
                                 // Check if owner_user_id exists and matches
                                 if (Schema::hasColumn($tableName, 'owner_user_id')) {
                                     $fallbackQ->orWhere('owner_user_id', $userId);
                                 }
                                 // Check if uploaded_by exists and matches (for files)
                                 if (Schema::hasColumn($tableName, 'uploaded_by')) {
                                     $fallbackQ->orWhere('uploaded_by', $userId);
                                 }
                             });
                    });
                });
            } else {
                // user_id column doesn't exist yet, use old fields
                return $query->where(function($q) use ($userId, $tableName) {
                    // Check if assigned_user_id exists and matches
                    if (Schema::hasColumn($tableName, 'assigned_user_id')) {
                        $q->where('assigned_user_id', $userId);
                    }
                    // Check if owner_user_id exists and matches
                    if (Schema::hasColumn($tableName, 'owner_user_id')) {
                        $q->orWhere('owner_user_id', $userId);
                    }
                    // Check if uploaded_by exists and matches (for files)
                    if (Schema::hasColumn($tableName, 'uploaded_by')) {
                        $q->orWhere('uploaded_by', $userId);
                    }
                });
            }
        } catch (\Exception $e) {
            \Log::error('PermissionHelper::filterByUserId error: ' . $e->getMessage(), [
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

