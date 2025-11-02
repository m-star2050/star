-- Add missing columns to crm_files table
-- Run this SQL directly in your database if the migration doesn't work
-- Note: Check if columns exist first to avoid errors

-- Check existing columns first:
-- SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'crm_files';

-- Then run only the columns that don't exist:

ALTER TABLE crm_files ADD COLUMN stored_name VARCHAR(255) NULL AFTER original_name;
ALTER TABLE crm_files ADD COLUMN file_type VARCHAR(255) NULL AFTER stored_name;
ALTER TABLE crm_files ADD COLUMN file_path VARCHAR(255) NULL AFTER file_type;
ALTER TABLE crm_files ADD COLUMN file_size INT NULL AFTER file_path;
ALTER TABLE crm_files ADD COLUMN description TEXT NULL AFTER uploaded_by;

-- If you have old columns, copy data (uncomment these if needed):
-- UPDATE crm_files SET file_type = mime WHERE file_type IS NULL AND mime IS NOT NULL;
-- UPDATE crm_files SET file_path = path WHERE file_path IS NULL AND path IS NOT NULL;
-- UPDATE crm_files SET file_size = size WHERE file_size IS NULL AND size IS NOT NULL;
-- UPDATE crm_files SET stored_name = file_name WHERE stored_name IS NULL AND file_name IS NOT NULL;

