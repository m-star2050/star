# Role Management Guide

## Automatic Role Assignment

### Registration Flow

1. **First User (Admin)**
   - The very first user to register automatically receives the **Admin** role
   - Admin has full access to all CRM features
   - No manual intervention required

2. **Subsequent Users (Executive)**
   - All other users who register automatically receive the **Executive** role
   - Executive has limited access (can only view/edit assigned records)
   - Perfect for team members and customers

## Changing User Roles

### Method 1: Web Interface (Recommended) ⭐

1. **Log in as Admin user** (first registered user)
2. **Navigate to**: `/crm/user-roles`
3. **Select a role** from the dropdown for any user
4. **Role is updated immediately**

This is the easiest and most professional method for your customers!

### Method 2: Laravel Tinker (For Developers)

```bash
php artisan tinker
```

```php
// Get user by email
$user = App\Models\User::where('email', 'user@example.com')->first();

// Remove all existing roles
$user->roles()->detach();

// Assign Manager role
$user->assignRole('Manager');

// Or assign Admin role
$user->assignRole('Admin');

// Or assign Executive role
$user->assignRole('Executive');

// Verify the role was assigned
$user->hasRole('Manager'); // Should return true
$user->getRoleNames(); // See all assigned roles
```

### Method 3: Direct Database (Not Recommended)

Only use if other methods don't work:

```sql
-- 1. Find user ID
SELECT id, email FROM users WHERE email = 'user@example.com';

-- 2. Find role ID
SELECT id, name FROM roles WHERE name = 'Manager';

-- 3. Remove existing roles
DELETE FROM model_has_roles WHERE model_id = USER_ID AND model_type = 'App\\Models\\User';

-- 4. Assign new role (replace USER_ID and ROLE_ID)
INSERT INTO model_has_roles (role_id, model_type, model_id) 
VALUES (ROLE_ID, 'App\\Models\\User', USER_ID);
```

## Role Capabilities

### Admin
- ✅ Full access to all CRM features
- ✅ Can view, create, edit, delete all records
- ✅ Can export data
- ✅ Can manage user roles
- ✅ Can access all reports

### Manager
- ✅ **Sees ALL records** in the system (no filtering)
- ✅ Can view and manage team data (all records)
- ✅ Can export data (has export permissions)
- ✅ Can delete records (has delete permissions)
- ✅ Can use bulk delete
- ✅ Cannot manage user roles (Admin only)
- ✅ Can access all reports with full data

### Executive
- ✅ **Sees ONLY records assigned to them** (filtered view)
- ✅ Can create new records
- ✅ Can edit only their assigned records
- ❌ **Cannot delete records** (no delete permissions)
- ❌ **Cannot export data** (no export permissions)
- ❌ Cannot bulk delete
- ❌ Cannot manage user roles
- ✅ Can view reports (but only sees their own data)

**Key Difference:**
- **Manager** = Team visibility (sees everything, can manage everything)
- **Executive** = Individual contributor (sees only assigned records, cannot delete/export)

## Quick Examples

### Assign Manager Role to User

**Using Web Interface:**
1. Go to `/crm/user-roles`
2. Find the user
3. Select "Manager" from dropdown
4. Done!

**Using Tinker:**
```php
$user = App\Models\User::where('email', 'manager@example.com')->first();
$user->roles()->detach();
$user->assignRole('Manager');
```

### Check User's Current Role

```php
$user = App\Models\User::where('email', 'user@example.com')->first();
$user->getRoleNames(); // Returns collection of role names
$user->hasRole('Manager'); // Returns true/false
```

### List All Users with Their Roles

```php
User::with('roles')->get()->map(function($user) {
    return [
        'name' => $user->name,
        'email' => $user->email,
        'roles' => $user->getRoleNames()->toArray()
    ];
});
```

## Summary

- **First Registration** → **Admin** (automatic)
- **Subsequent Registrations** → **Executive** (automatic)
- **Change Roles** → Use `/crm/user-roles` web interface (easiest) or Tinker

The web interface at `/crm/user-roles` is the recommended method for managing roles, especially for customers who may not be familiar with command-line tools.

