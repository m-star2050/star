# Fix Missing Database Columns - Quick Guide

## The Problem
The `crm_files` table is missing the `file_type` column (and possibly others). This happens when migrations haven't run or the table structure is outdated.

## Solution: Run the Fix Migration

### Step 1: Navigate to Laravel root
```bash
cd public_html
```

### Step 2: Run the new migration to add missing columns
```bash
php artisan migrate
```

This will add any missing columns to the `crm_files` table without affecting existing data.

### Step 3: Verify the columns exist (Optional)
```bash
php artisan tinker
```
Then in tinker, run:
```php
Schema::getColumnListing('crm_files');
exit
```

This will show you all columns in the table. You should see `file_type`, `file_name`, and `path` in the list.

### Alternative: If Migration Fails

If the migration fails, you can manually check and add columns using this SQL (run in phpMyAdmin or database tool):

```sql
-- Check if columns exist first
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'crm_files';

-- Add missing columns (only run if they don't exist)
ALTER TABLE `crm_files` 
ADD COLUMN `file_type` VARCHAR(255) NULL AFTER `file_path`,
ADD COLUMN `file_name` VARCHAR(255) NULL AFTER `stored_name`,
ADD COLUMN `path` VARCHAR(255) NULL AFTER `file_path`;
```

## After Running the Fix

1. Clear cache:
```bash
php artisan config:clear
php artisan cache:clear
```

2. Test the CRM files page:
   - Visit: `https://yourdomain.com/crm/files`
   - The error should be gone!

