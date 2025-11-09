<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_contacts')) {
            Schema::create('crm_contacts', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('company')->nullable();
                $table->string('email')->nullable()->index();
                $table->string('phone')->nullable()->index();
                $table->unsignedBigInteger('assigned_user_id')->nullable()->index();
                $table->enum('status', ['active', 'archived'])->default('active')->index();
                $table->json('tags')->nullable();
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_contacts');
    }
};


