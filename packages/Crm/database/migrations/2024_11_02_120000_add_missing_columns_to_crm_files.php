<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('crm_files')) {
            $database = DB::connection()->getDatabaseName();
            $columns = DB::select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?", [$database, 'crm_files']);
            $existingColumns = array_map(function($col) {
                return $col->COLUMN_NAME;
            }, $columns);
            
            // Add stored_name if missing
            if (!in_array('stored_name', $existingColumns)) {
                DB::statement('ALTER TABLE crm_files ADD COLUMN stored_name VARCHAR(255) NULL AFTER original_name');
            }
            
            // Add file_type if missing
            if (!in_array('file_type', $existingColumns)) {
                DB::statement('ALTER TABLE crm_files ADD COLUMN file_type VARCHAR(255) NULL AFTER stored_name');
                
                // Copy data from mime to file_type if mime exists
                if (in_array('mime', $existingColumns)) {
                    DB::statement('UPDATE crm_files SET file_type = mime WHERE file_type IS NULL AND mime IS NOT NULL');
                }
            }
            
            // Add file_path if missing
            if (!in_array('file_path', $existingColumns)) {
                DB::statement('ALTER TABLE crm_files ADD COLUMN file_path VARCHAR(255) NULL AFTER file_type');
                
                // Copy data from path to file_path if path exists
                if (in_array('path', $existingColumns)) {
                    DB::statement('UPDATE crm_files SET file_path = path WHERE file_path IS NULL AND path IS NOT NULL');
                }
            }
            
            // Add file_size if missing
            if (!in_array('file_size', $existingColumns)) {
                DB::statement('ALTER TABLE crm_files ADD COLUMN file_size INT NULL AFTER file_path');
                
                // Copy data from size to file_size if size exists
                if (in_array('size', $existingColumns)) {
                    DB::statement('UPDATE crm_files SET file_size = size WHERE file_size IS NULL AND size IS NOT NULL');
                }
            }
            
            // Add description if missing
            if (!in_array('description', $existingColumns)) {
                DB::statement('ALTER TABLE crm_files ADD COLUMN description TEXT NULL AFTER uploaded_by');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('crm_files')) {
            $database = DB::connection()->getDatabaseName();
            $columns = DB::select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?", [$database, 'crm_files']);
            $existingColumns = array_map(function($col) {
                return $col->COLUMN_NAME;
            }, $columns);
            
            if (in_array('stored_name', $existingColumns)) {
                DB::statement('ALTER TABLE crm_files DROP COLUMN stored_name');
            }
            if (in_array('file_type', $existingColumns)) {
                DB::statement('ALTER TABLE crm_files DROP COLUMN file_type');
            }
            if (in_array('file_path', $existingColumns)) {
                DB::statement('ALTER TABLE crm_files DROP COLUMN file_path');
            }
            if (in_array('file_size', $existingColumns)) {
                DB::statement('ALTER TABLE crm_files DROP COLUMN file_size');
            }
            if (in_array('description', $existingColumns)) {
                DB::statement('ALTER TABLE crm_files DROP COLUMN description');
            }
        }
    }
};

