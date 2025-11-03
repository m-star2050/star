# CRM Module Integration Guide

## Step-by-Step Integration Commands

After uploading the CRM package to `public_html/packages/Crm`, follow these steps:

### Step 1: Verify Package Structure
Ensure the CRM package is uploaded to: `public_html/packages/Crm/`

### Step 2: Update Composer Autoload
The `composer.json` should include the `Packages\\` namespace. If not already present, add it:

```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Packages\\": "packages/"
    }
}
```

### Step 3: Run Composer Commands
```bash
cd public_html
composer dump-autoload
```

### Step 4: Register Service Provider
Ensure `bootstrap/providers.php` includes:
```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    Packages\Crm\Providers\CrmServiceProvider::class,
];
```

### Step 5: Run Migrations
```bash
php artisan migrate
```

### Step 6: Clear All Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 7: Optimize (Optional - for production)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Quick Integration Command (All-in-One)
Run this single command sequence:

```bash
cd public_html && composer dump-autoload && php artisan migrate && php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear
```

## Verification
After running the commands, test the integration:
- Visit: `https://yourdomain.com/crm` - Should show "CRM Module Connected!"
- Visit: `https://yourdomain.com/crm/files` - Should show the files management page

## Troubleshooting

### If routes don't work:
1. Check `bootstrap/providers.php` has the service provider registered
2. Run `php artisan route:list | grep crm` to verify routes are loaded
3. Clear route cache: `php artisan route:clear`

### If migrations fail:
1. Check database connection in `.env`
2. Verify migration files exist in `packages/Crm/database/migrations/`
3. Run `php artisan migrate:status` to see migration status

### If views don't load:
1. Verify views exist in `packages/Crm/resources/views/`
2. Clear view cache: `php artisan view:clear`
3. Check service provider is loading views correctly

