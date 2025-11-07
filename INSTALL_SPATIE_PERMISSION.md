# Install Spatie Laravel Permission Package

## ⚠️ IMPORTANT: Required Package

The CRM system requires the **Spatie Laravel Permission** package for role-based access control (RBAC). 

**Current Status**: The code will work without crashing, but RBAC features will not function until the package is installed.

**Error You Might See**: "Class 'Spatie\Permission\Models\Role' not found"

## Quick Fix

Run these commands in your terminal (Laragon Terminal or Command Prompt):

```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
php artisan optimize:clear
```

That's it! After installation, registration and login will work with automatic role assignment.

## Role Assignment Summary

- **First User (First Registration)**: Automatically gets **Admin** role
- **Subsequent Users**: Automatically get **Executive** role
- **Changing Roles**: Use one of the methods below

## How to Change User Roles

### Method 1: Using the Web Interface (Recommended)

1. Log in as an Admin user
2. Navigate to: `/crm/user-roles`
3. Use the dropdown to select a role for any user
4. The role will be updated immediately

### Method 2: Using Laravel Tinker

```bash
php artisan tinker
```

Then in Tinker:

```php
// Get user by email
$user = App\Models\User::where('email', 'user@example.com')->first();

// Remove existing roles
$user->roles()->detach();

// Assign new role (choose one)
$user->assignRole('Admin');     // Full access
$user->assignRole('Manager');   // Team management
$user->assignRole('Executive'); // Limited access

// Verify the role
$user->hasRole('Manager'); // Should return true
$user->getRoleNames(); // See all roles
```

### Method 3: Direct Database Update (Not Recommended)

Only use this if Tinker or web interface don't work:

```sql
-- Find user ID
SELECT id, email FROM users WHERE email = 'user@example.com';

-- Find role ID
SELECT id, name FROM roles WHERE name = 'Manager';

-- Assign role (replace user_id and role_id with actual IDs)
INSERT INTO model_has_roles (role_id, model_type, model_id) 
VALUES (role_id, 'App\\Models\\User', user_id);
```

## Installation Steps

1. **Install the package via Composer:**
   ```bash
   composer require spatie/laravel-permission
   ```

2. **Publish the migration files:**
   ```bash
   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
   ```

3. **Run the migrations:**
   ```bash
   php artisan migrate
   ```

4. **Clear caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

5. **Seed the roles and permissions (optional - will auto-seed on first registration):**
   ```bash
   php artisan db:seed --class="Packages\Crm\Database\Seeders\CrmRolePermissionSeeder"
   ```

## Verify Installation

After installation, you can verify it's working by:

1. Registering a new user (first user will automatically get Admin role)
2. Checking the `roles` and `permissions` tables in your database
3. Verifying that the user has the correct role assigned

## Troubleshooting

If you encounter any issues:

1. Make sure the package is installed: `composer show spatie/laravel-permission`
2. Check that migrations have run: `php artisan migrate:status`
3. Verify the User model has the `HasRoles` trait (already added)
4. Clear all caches: `php artisan optimize:clear`

## Manual Installation via Laragon

If you're using Laragon:

1. Open Laragon Terminal
2. Navigate to your project directory: `cd C:\laragon\www\afli`
3. Run: `composer require spatie/laravel-permission`
4. Run: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
5. Run: `php artisan migrate`
6. Clear caches: `php artisan optimize:clear`

After installation, registration and login will work properly with automatic role assignment!

