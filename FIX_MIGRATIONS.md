# Fix: Migrations Not Loading (Contacts, Leads, Files, etc.)

## The Problem

When running `php artisan migrate:fresh`, only the `cache` table migration runs. The CRM package migrations (contacts, leads, files, users, roles, permissions) are not being discovered or executed.

## Root Cause

1. **Service Provider Not Booting**: The `ModuleServiceProvider` referenced in `bootstrap/providers.php` doesn't exist, which may prevent other providers from loading properly.
2. **Cache Issue**: Laravel may have cached the service provider configuration, preventing migrations from being discovered.
3. **Migration Discovery**: The CRM package migrations loaded via `loadMigrationsFrom()` may not be discovered if service providers don't boot correctly.

## The Fix

### Step 1: Fixed Service Providers

I've updated `bootstrap/providers.php` to comment out the non-existent `ModuleServiceProvider`:

```php
return [
    App\Providers\AppServiceProvider::class,
    Packages\Crm\Providers\CrmServiceProvider::class,
    // Module\Providers\ModuleServiceProvider::class, // Commented out - file doesn't exist
];
```

### Step 2: Run Complete Migration Script

Upload `run_all_migrations.sh` to your server and run:

```bash
chmod +x run_all_migrations.sh
./run_all_migrations.sh
```

### Step 3: Manual Fix (If Script Doesn't Work)

```bash
cd /home/crm/public_html

# 1. Clear ALL caches (CRITICAL!)
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# 2. Verify service provider is registered
php artisan tinker
# In tinker:
$providers = require 'bootstrap/providers.php';
print_r($providers);
# Should show CrmServiceProvider in the list
exit

# 3. Verify migrations are discovered
php verify_migrations.php
# Should show all CRM migrations

# 4. Run migrations
php artisan migrate --force -v

# 5. Verify tables were created
php artisan tinker
# In tinker:
Schema::hasTable('users'); // Should return true
Schema::hasTable('crm_contacts'); // Should return true
Schema::hasTable('crm_leads'); // Should return true
exit

# 6. Seed roles and permissions
php artisan tinker
$seeder = new \Packages\Crm\database\seeders\CrmRolePermissionSeeder();
$seeder->run();
exit
```

## What Should Happen

After running migrations, you should see output like:

```
INFO Running migrations.
2025_11_08_105412_create_cache_table .......... DONE
2025_01_01_000000_create_users_table .......... DONE
2025_01_01_000001_create_cache_table .......... DONE
2025_01_01_000002_create_jobs_table .......... DONE
2025_11_07_140835_create_permission_tables ..... DONE
crm_contacts_table ............................ DONE
crm_leads_table ............................... DONE
crm_tasks_table ............................... DONE
crm_pipelines_table ........................... DONE
crm_files_table ............................... DONE
crm_reports_table ............................. DONE
crm_roles_table ............................... DONE
```

## Verification

After migrations complete, verify these tables exist:

- ✅ `users`
- ✅ `roles`
- ✅ `permissions`
- ✅ `model_has_roles`
- ✅ `model_has_permissions`
- ✅ `role_has_permissions`
- ✅ `crm_contacts`
- ✅ `crm_leads`
- ✅ `crm_tasks`
- ✅ `crm_pipelines`
- ✅ `crm_files`
- ✅ `crm_reports`
- ✅ `crm_roles`

## Troubleshooting

### If migrations still don't show:

1. **Check service provider is booting:**
   ```bash
   php artisan tinker
   app()->getProvider('Packages\Crm\Providers\CrmServiceProvider');
   # Should return the service provider instance, not null
   ```

2. **Check migration paths:**
   ```bash
   php artisan tinker
   app('migrator')->paths();
   # Should include packages/Crm/database/migrations
   ```

3. **Manually check migration files:**
   ```bash
   ls -la packages/Crm/database/migrations/
   # Should show all CRM migration files
   ```

4. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   # Look for any errors during migration
   ```

### If you get "Class not found" errors:

```bash
composer dump-autoload
php artisan config:clear
php artisan optimize:clear
```

### If tables still don't exist after migrations:

1. Check database connection in `.env`
2. Verify database user has CREATE TABLE permissions
3. Check for errors in `storage/logs/laravel.log`
4. Try running migrations with `-v` flag for verbose output

## Files Changed

1. ✅ `bootstrap/providers.php` - Commented out non-existent ModuleServiceProvider
2. ✅ `run_all_migrations.sh` - Complete migration script
3. ✅ `verify_migrations.php` - Migration verification script

## After Fix

Once migrations run successfully:
- ✅ Registration will work
- ✅ Login will work
- ✅ You can access the workspace (files page)
- ✅ All CRM features will be available
- ✅ Contacts, leads, files, etc. can be created and stored in the database

