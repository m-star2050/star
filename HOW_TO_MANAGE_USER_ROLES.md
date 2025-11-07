# How to Manage User Roles (Admin Guide)

## Step-by-Step Guide: How Admin Can Assign Roles to Users

### Prerequisites

1. **You must be logged in as an Admin user**
   - The first registered user automatically gets Admin role
   - If you're not an Admin, another Admin needs to assign it to you first

2. **Spatie Permission package must be installed**
   ```bash
   composer require spatie/laravel-permission
   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
   php artisan migrate
   ```

---

## Method 1: Using the Web Interface (Easiest) ⭐

### Step 1: Log in as Admin
1. Go to `/crm/login` or your login page
2. Log in with an Admin account

### Step 2: Access User Roles Page

**Option A: Via Sidebar (Recommended)**
1. Look at the left sidebar in any CRM page
2. Scroll down to find the **"Administration"** section
3. Click on **"User Roles"** link
   - This link is **ONLY visible to Admin users**
   - Manager and Executive users won't see this link

**Option B: Direct URL**
1. Type in your browser: `http://your-domain.com/crm/user-roles`
   - Or: `http://afli.test/crm/user-roles`
   - Or: `http://localhost/crm/user-roles`

### Step 3: View All Users
- You'll see a table with all users in the system
- Columns show: User Name, Email, Current Role, Actions

### Step 4: Change a User's Role
1. Find the user you want to change in the table
2. Look at the **"Actions"** column (rightmost column)
3. You'll see a **dropdown menu** with options:
   - `-- Select Role --`
   - `Admin`
   - `Manager`
   - `Executive`
4. **Current role** is already selected in the dropdown
5. Click the dropdown and select a new role:
   - To make someone a Manager: Select `Manager`
   - To make someone an Executive: Select `Executive`
   - To make someone an Admin: Select `Admin`
6. A confirmation dialog will appear
7. Click **"OK"** to confirm
8. The role will be updated immediately
9. The page will refresh to show the new role

### Step 5: Verify the Change
- Check the "Current Role" column - it should show the new role with a colored badge:
  - **Purple badge** = Admin
  - **Blue badge** = Manager
  - **Green badge** = Executive

---

## Method 2: Using Laravel Tinker (For Developers)

If you prefer command-line or the web interface isn't working:

### Step 1: Open Tinker
```bash
php artisan tinker
```

### Step 2: Find the User
```php
$user = App\Models\User::where('email', 'user@example.com')->first();
```

### Step 3: Remove Existing Roles
```php
$user->roles()->detach();
```

### Step 4: Assign New Role
```php
// Assign Manager role
$user->assignRole('Manager');

// OR assign Executive role
$user->assignRole('Executive');

// OR assign Admin role
$user->assignRole('Admin');
```

### Step 5: Verify
```php
$user->getRoleNames(); // Should show the new role
$user->hasRole('Manager'); // Should return true if Manager
```

---

## Visual Guide: What You'll See

### 1. Sidebar Navigation (Admin Only)

```
┌─────────────────────────┐
│  Navigation             │
├─────────────────────────┤
│  📁 Contacts            │
│  📁 Leads               │
│  📁 Tasks               │
│  📁 Pipeline            │
│  📁 Reports             │
│  📁 Files               │
├─────────────────────────┤
│  Administration  ⚙️     │ ← Only Admin sees this
├─────────────────────────┤
│  👥 User Roles          │ ← Click here!
└─────────────────────────┘
```

### 2. User Roles Page

```
┌──────────────────────────────────────────────────────────┐
│  User Role Management                                    │
│  Manage user roles and permissions                       │
├──────────────────────────────────────────────────────────┤
│  User          │ Email            │ Role      │ Actions  │
├──────────────────────────────────────────────────────────┤
│  John Doe      │ john@example.com │ [Admin]   │ [▼]      │
│  Jane Smith    │ jane@example.com │ [Executive]│ [▼]     │
│  Bob Wilson    │ bob@example.com  │ [Manager] │ [▼]      │
└──────────────────────────────────────────────────────────┘
                         ↑
                    Dropdown menu
```

### 3. Dropdown Menu

When you click the dropdown, you'll see:
```
┌─────────────────┐
│ -- Select Role -│
│ Admin        ✓  │ ← Currently selected
│ Manager         │
│ Executive       │
└─────────────────┘
```

---

## Troubleshooting

### Problem: "403 Unauthorized" Error

**Cause**: You're not logged in as an Admin user.

**Solution**:
1. Check if you're logged in
2. Verify you have Admin role:
   ```bash
   php artisan tinker
   ```
   ```php
   $user = App\Models\User::where('email', 'your-email@example.com')->first();
   $user->getRoleNames(); // Should show "Admin"
   ```
3. If you don't have Admin role, ask another Admin to assign it to you

### Problem: "User Roles" Link Not in Sidebar

**Cause**: You're not an Admin user, or the sidebar hasn't refreshed.

**Solution**:
1. Refresh the page (F5)
2. Log out and log back in
3. Check if you have Admin role (see above)
4. Use direct URL: `/crm/user-roles`

### Problem: Dropdown Shows "No Role Assigned"

**Cause**: Spatie package not installed or roles not seeded.

**Solution**:
1. Install Spatie package:
   ```bash
   composer require spatie/laravel-permission
   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
   php artisan migrate
   ```
2. Seed roles:
   ```bash
   php artisan db:seed --class="Packages\Crm\Database\Seeders\CrmRolePermissionSeeder"
   ```

### Problem: Can't Change Roles (Dropdown Not Working)

**Cause**: JavaScript error or CSRF token issue.

**Solution**:
1. Check browser console for errors (F12)
2. Clear browser cache
3. Try refreshing the page
4. Make sure you're using a modern browser (Chrome, Firefox, Edge)

### Problem: Role Change Doesn't Save

**Cause**: Permission issue or database error.

**Solution**:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify Spatie package is installed
3. Check database connection
4. Try using Tinker method instead

---

## Example Workflow

### Scenario: You want to make a user a Manager

1. **Log in as Admin**
   - Go to `/crm/login`
   - Enter your Admin credentials

2. **Navigate to User Roles**
   - Click "User Roles" in the sidebar (under Administration)
   - Or go to `/crm/user-roles`

3. **Find the User**
   - Look through the table
   - Find the user by name or email

4. **Change Role**
   - Click the dropdown in the "Actions" column
   - Select "Manager"
   - Confirm the change

5. **Verify**
   - The "Current Role" column should show a blue "Manager" badge
   - The user can now log in and see all team data

---

## What Happens After Role Assignment

### Immediate Effects

1. **User must log out and log back in** for changes to take effect
2. **Data visibility changes**:
   - Manager: Sees all records
   - Executive: Sees only assigned records
3. **Button visibility changes**:
   - Manager: Sees Delete/Export buttons
   - Executive: Doesn't see Delete/Export buttons

### Testing the Change

1. **Log out** as Admin
2. **Log in** as the user whose role you changed
3. **Check**:
   - What data they can see
   - What buttons are visible
   - What actions they can perform

---

## Security Notes

- ✅ Only Admin users can access this page
- ✅ Manager and Executive users get "403 Unauthorized" if they try to access
- ✅ Role changes are logged in Laravel logs
- ✅ Changes take effect immediately after user logs back in

---

## Quick Reference

| Action | How to Do It |
|--------|--------------|
| **Access User Roles** | Sidebar → Administration → User Roles |
| **Direct URL** | `/crm/user-roles` |
| **Change Role** | Click dropdown → Select role → Confirm |
| **Verify Role** | Check colored badge in "Current Role" column |
| **Troubleshoot** | Check if Admin, check Spatie installation |

---

## Summary

1. **Log in as Admin**
2. **Click "User Roles" in sidebar** (under Administration)
3. **Find user in table**
4. **Select new role from dropdown**
5. **Confirm change**
6. **Done!** User needs to log out and back in for changes to apply.

The interface is simple and user-friendly - just a table with a dropdown for each user!

