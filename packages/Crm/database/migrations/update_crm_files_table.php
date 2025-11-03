<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if table exists and add missing columns
        if (Schema::hasTable('crm_files')) {
            // Check which columns exist BEFORE modifying the table
            $hasStoredName = Schema::hasColumn('crm_files', 'stored_name');
            $hasFilePath = Schema::hasColumn('crm_files', 'file_path');
            
            // Add file_path first if it doesn't exist
            if (!$hasFilePath) {
                Schema::table('crm_files', function (Blueprint $table) use ($hasStoredName) {
                    if ($hasStoredName) {
                        $table->string('file_path')->nullable()->after('stored_name');
                    } else {
                        $table->string('file_path')->nullable();
                    }
                });
                $hasFilePath = true; // Update flag after adding
            }
            
            // Add file_name if it doesn't exist
            if (!Schema::hasColumn('crm_files', 'file_name')) {
                Schema::table('crm_files', function (Blueprint $table) use ($hasStoredName) {
                    if ($hasStoredName) {
                        $table->string('file_name')->nullable()->after('stored_name');
                    } else {
                        $table->string('file_name')->nullable();
                    }
                });
            }
            
            // Add file_type if it doesn't exist (add after file_path now that we've ensured it exists)
            if (!Schema::hasColumn('crm_files', 'file_type')) {
                Schema::table('crm_files', function (Blueprint $table) use ($hasFilePath) {
                    if ($hasFilePath) {
                        $table->string('file_type')->nullable()->after('file_path');
                    } else {
                        $table->string('file_type')->nullable();
                    }
                });
            }
            
            // Add path if it doesn't exist (legacy column)
            if (!Schema::hasColumn('crm_files', 'path')) {
                Schema::table('crm_files', function (Blueprint $table) use ($hasFilePath) {
                    if ($hasFilePath) {
                        $table->string('path')->nullable()->after('file_path');
                    } else {
                        $table->string('path')->nullable();
                    }
                });
            }
        } else {
            // If table doesn't exist, create it with all columns
            Schema::create('crm_files', function (Blueprint $table) {
                $table->id();
                $table->string('original_name');
                $table->string('stored_name');
                $table->string('file_name')->nullable();
                $table->string('file_path');
                $table->string('path')->nullable();
                $table->string('file_type')->nullable();
                $table->integer('file_size')->nullable();
                $table->string('linked_type')->nullable();
                $table->unsignedBigInteger('linked_id')->nullable();
                $table->unsignedBigInteger('uploaded_by')->nullable();
                $table->text('description')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->index(['linked_type', 'linked_id']);
                $table->index('uploaded_by');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't drop columns in down() to avoid data loss
        // If you need to rollback, manually remove the columns
    }
};

