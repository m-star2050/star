# CRM Setup Instructions

## Automatic Role Assignment

The CRM system now automatically handles role assignment during user registration:

### Registration Flow

1. **First User (Admin)**
   - The first user to register automatically receives the **Admin** role
   - Admin has full access to all CRM features
   - No manual intervention required

2. **Subsequent Users (Executive)**
   - All other users automatically receive the **Executive** role
   - Executive role has limited access (can only view/edit assigned records)
   - Perfect for team members and customers

3. **Permissions Auto-Seeding**
   - Permissions and roles are automatically seeded on first registration
   - No need to run seeders manually
   - System is ready to use immediately

### Role Capabilities

**Admin:**
- Full access to all CRM features
- Can view, create, edit, delete all records
- Can export data
- Can manage all users and data

**Manager:**
- Can view and manage team data (all records)
- Can export data
- Cannot delete (unless assigned Manager role manually)

**Executive:**
- Can only access records assigned to them
- Can create new records
- Can edit/delete only their assigned records
- Cannot export or bulk delete

### Testing the System

1. **First Registration (Admin):**
   ```
   - Go to /register
   - Create an account
   - You will automatically be assigned Admin role
   - You can now access all CRM features
   ```

2. **Additional Users (Executive):**
   ```
   - Register additional accounts
   - They will automatically receive Executive role
   - They can only see their assigned records
   ```

### Changing User Roles (Admin Only)

If you need to change a user's role after registration, use Tinker:

```bash
php artisan tinker
```

```php
// Get user
$user = App\Models\User::where('email', 'user@example.com')->first();

// Remove existing roles
$user->roles()->detach();

// Assign new role
$user->assignRole('Manager'); // or 'Admin', 'Executive'

// Verify
$user->hasRole('Manager'); // Should return true
```

### Existing Users

If you have existing users who registered before RBAC was implemented:
- They will automatically receive a role on their next login
- The oldest user will receive Admin role
- Other users will receive Executive role

### Troubleshooting

If you encounter permission errors:
1. Clear application cache: `php artisan cache:clear`
2. Clear permission cache: `php artisan permission:cache-reset`
3. Ensure the User model has the `HasRoles` trait (already added)
4. Verify roles exist: `php artisan tinker` then `Spatie\Permission\Models\Role::all()`

### Manual Seeding (Optional)

If you need to manually seed permissions (usually not needed):

```bash
php artisan db:seed --class="Packages\Crm\Database\Seeders\CrmRolePermissionSeeder"
```

This will create the roles and permissions but won't assign roles to users (registration handles that).

