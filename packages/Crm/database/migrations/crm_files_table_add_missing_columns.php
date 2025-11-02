<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            // Check and add columns using raw SQL to avoid issues with Schema::hasColumn inside table callback
            $columns = $this->getTableColumns('crm_files');
            
            if (!in_array('stored_name', $columns)) {
                Schema::table('crm_files', function (Blueprint $table) {
                    $table->string('stored_name')->nullable();
                });
            }
            
            if (!in_array('file_type', $columns)) {
                Schema::table('crm_files', function (Blueprint $table) {
                    $table->string('file_type')->nullable();
                });
            }
            
            if (!in_array('file_path', $columns)) {
                Schema::table('crm_files', function (Blueprint $table) {
                    $table->string('file_path')->nullable();
                });
            }
            
            if (!in_array('file_size', $columns)) {
                Schema::table('crm_files', function (Blueprint $table) {
                    $table->integer('file_size')->nullable();
                });
            }
            
            if (!in_array('description', $columns)) {
                Schema::table('crm_files', function (Blueprint $table) {
                    $table->text('description')->nullable();
                });
            }
            
            // Migrate data from old column names to new ones if they exist
            $this->migrateColumnData($columns);
        }
    }

    /**
     * Get table columns using raw query
     */
    private function getTableColumns(string $table): array
    {
        $database = DB::connection()->getDatabaseName();
        $columns = DB::select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?", [$database, $table]);
        return array_map(function($column) {
            return $column->COLUMN_NAME;
        }, $columns);
    }

    /**
     * Migrate data from old column structure to new one
     */
    private function migrateColumnData(array $columns): void
    {
        if (in_array('mime', $columns) && in_array('file_type', $columns)) {
            DB::statement('UPDATE crm_files SET file_type = mime WHERE file_type IS NULL AND mime IS NOT NULL');
        }
        
        if (in_array('path', $columns) && in_array('file_path', $columns)) {
            DB::statement('UPDATE crm_files SET file_path = path WHERE file_path IS NULL AND path IS NOT NULL');
        }
        
        if (in_array('size', $columns) && in_array('file_size', $columns)) {
            DB::statement('UPDATE crm_files SET file_size = size WHERE file_size IS NULL AND size IS NOT NULL');
        }
        
        if (in_array('file_name', $columns) && in_array('stored_name', $columns)) {
            DB::statement('UPDATE crm_files SET stored_name = file_name WHERE stored_name IS NULL AND file_name IS NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('crm_files')) {
            $columns = $this->getTableColumns('crm_files');
            
            Schema::table('crm_files', function (Blueprint $table) use ($columns) {
                if (in_array('stored_name', $columns)) {
                    $table->dropColumn('stored_name');
                }
                if (in_array('file_type', $columns)) {
                    $table->dropColumn('file_type');
                }
                if (in_array('file_path', $columns)) {
                    $table->dropColumn('file_path');
                }
                if (in_array('file_size', $columns)) {
                    $table->dropColumn('file_size');
                }
                if (in_array('description', $columns)) {
                    $table->dropColumn('description');
                }
            });
        }
    }
};

