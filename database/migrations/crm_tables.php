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
        // Add user_id to crm_contacts table
        if (Schema::hasTable('crm_contacts') && !Schema::hasColumn('crm_contacts', 'user_id')) {
            Schema::table('crm_contacts', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->index('user_id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
            
            // Migrate data from assigned_user_id to user_id if assigned_user_id exists
            if (Schema::hasColumn('crm_contacts', 'assigned_user_id')) {
                \DB::statement('UPDATE crm_contacts SET user_id = assigned_user_id WHERE assigned_user_id IS NOT NULL AND user_id IS NULL');
            }
        }

        // Add user_id to crm_leads table
        if (Schema::hasTable('crm_leads') && !Schema::hasColumn('crm_leads', 'user_id')) {
            Schema::table('crm_leads', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->index('user_id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
            
            // Migrate data from assigned_user_id to user_id if assigned_user_id exists
            if (Schema::hasColumn('crm_leads', 'assigned_user_id')) {
                \DB::statement('UPDATE crm_leads SET user_id = assigned_user_id WHERE assigned_user_id IS NOT NULL AND user_id IS NULL');
            }
        }

        // Add user_id to crm_tasks table
        if (Schema::hasTable('crm_tasks') && !Schema::hasColumn('crm_tasks', 'user_id')) {
            Schema::table('crm_tasks', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->index('user_id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
            
            // Migrate data from assigned_user_id to user_id if assigned_user_id exists
            if (Schema::hasColumn('crm_tasks', 'assigned_user_id')) {
                \DB::statement('UPDATE crm_tasks SET user_id = assigned_user_id WHERE assigned_user_id IS NOT NULL AND user_id IS NULL');
            }
        }

        // Add user_id to crm_pipelines table
        if (Schema::hasTable('crm_pipelines') && !Schema::hasColumn('crm_pipelines', 'user_id')) {
            Schema::table('crm_pipelines', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->index('user_id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
            
            // Migrate data from owner_user_id to user_id if owner_user_id exists
            if (Schema::hasColumn('crm_pipelines', 'owner_user_id')) {
                \DB::statement('UPDATE crm_pipelines SET user_id = owner_user_id WHERE owner_user_id IS NOT NULL AND user_id IS NULL');
            }
        }

        // Add user_id to crm_files table
        if (Schema::hasTable('crm_files') && !Schema::hasColumn('crm_files', 'user_id')) {
            Schema::table('crm_files', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->index('user_id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
            
            // Migrate data from uploaded_by to user_id if uploaded_by exists
            if (Schema::hasColumn('crm_files', 'uploaded_by')) {
                \DB::statement('UPDATE crm_files SET user_id = uploaded_by WHERE uploaded_by IS NOT NULL AND user_id IS NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove user_id from crm_files
        if (Schema::hasTable('crm_files') && Schema::hasColumn('crm_files', 'user_id')) {
            Schema::table('crm_files', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        // Remove user_id from crm_pipelines
        if (Schema::hasTable('crm_pipelines') && Schema::hasColumn('crm_pipelines', 'user_id')) {
            Schema::table('crm_pipelines', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        // Remove user_id from crm_tasks
        if (Schema::hasTable('crm_tasks') && Schema::hasColumn('crm_tasks', 'user_id')) {
            Schema::table('crm_tasks', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        // Remove user_id from crm_leads
        if (Schema::hasTable('crm_leads') && Schema::hasColumn('crm_leads', 'user_id')) {
            Schema::table('crm_leads', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        // Remove user_id from crm_contacts
        if (Schema::hasTable('crm_contacts') && Schema::hasColumn('crm_contacts', 'user_id')) {
            Schema::table('crm_contacts', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};

