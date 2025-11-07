# CRM Role-Based Access Control (RBAC) Implementation Guide

## Overview
This document outlines the RBAC implementation for the CRM package using Spatie Laravel Permission package.

## Roles

1. **Admin**: Full access to all CRM features
2. **Manager**: View and manage team data (all records)
3. **Executive**: Access only assigned records (view, create, edit - no delete/export)

## Permissions

### Contacts
- `view contacts`
- `create contacts`
- `edit contacts`
- `delete contacts`
- `export contacts`

### Leads
- `view leads`
- `create leads`
- `edit leads`
- `delete leads`
- `export leads`

### Tasks
- `view tasks`
- `create tasks`
- `edit tasks`
- `delete tasks`
- `export tasks`

### Pipeline/Deals
- `view pipeline`
- `create pipeline`
- `edit pipeline`
- `delete pipeline`
- `export pipeline`

### Reports
- `view reports`
- `export reports`

### Files
- `view files`
- `upload files`
- `delete files`

## Setup Instructions

### 1. Install Spatie Laravel Permission
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### 2. Add HasRoles Trait to User Model
In your `App\Models\User` model, add:
```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    // ... rest of your code
}
```

### 3. Run the Seeder
```bash
php artisan db:seed --class="Packages\Crm\Database\Seeders\CrmRolePermissionSeeder"
```

### 4. Assign Roles to Users
```php
$user = User::find(1);
$user->assignRole('Admin'); // or 'Manager' or 'Executive'
```

## Implementation Details

### Middleware
- `crm.access`: Ensures user is authenticated and has a CRM role
- `crm.role:permission`: Checks if user has specific permission

### PermissionHelper
Located at `Packages\Crm\Helpers\PermissionHelper`, provides helper methods:
- `can($permission, $user = null)`: Check if user has permission
- `isAdmin($user = null)`: Check if user is admin
- `isManager($user = null)`: Check if user is manager
- `isExecutive($user = null)`: Check if user is executive
- `canAccessRecord($record, $user = null)`: Check if user can access specific record
- `filterByRole($query, $user = null, $assignedField, $ownerField)`: Filter query by role
- `canDelete($modelType, $user = null)`: Check if user can delete
- `canExport($modelType, $user = null)`: Check if user can export

### Controller Implementation
All controllers check permissions and filter data based on roles:
- Permission checks in each method
- Role-based data filtering (Executive sees only assigned records)
- Access record validation

### View Implementation
Views conditionally show/hide buttons based on permissions:
- Delete buttons: Only shown if user has delete permission
- Export buttons: Only shown if user has export permission
- Edit buttons: Only shown if user has edit permission and can access record

## Usage Examples

### In Controllers
```php
// Check permission
if (!auth()->user()->can('view contacts')) {
    abort(403, 'Unauthorized.');
}

// Filter by role
$query = PermissionHelper::filterByRole($query, auth()->user(), 'assigned_user_id');

// Check record access
if (!PermissionHelper::canAccessRecord($contact, auth()->user())) {
    abort(403, 'Unauthorized.');
}
```

### In Views
```php
@if(auth()->user()->can('delete contacts'))
    <button class="delete-btn">Delete</button>
@endif

@if(PermissionHelper::canExport('contact'))
    <button class="export-btn">Export</button>
@endif
```

## Role Behavior

### Admin
- Can view, create, edit, delete, and export all records
- No restrictions

### Manager
- Can view, create, edit, delete, and export all records (team data)
- Can manage all team members' records

### Executive
- Can view, create, edit only assigned records
- Cannot delete or export
- Records are filtered to show only those where `assigned_user_id` or `owner_user_id` matches user ID

## Security Notes

1. All routes are protected with `auth` and `crm.access` middleware
2. Permission checks are performed in controllers
3. Data is filtered at the query level for Executive role
4. Views conditionally render buttons based on permissions
5. Record access is validated before operations

