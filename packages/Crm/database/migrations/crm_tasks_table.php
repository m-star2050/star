<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->nullable()->index();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->index();
            $table->date('due_date')->nullable()->index();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending')->index();
            $table->unsignedBigInteger('assigned_user_id')->nullable()->index();
            $table->unsignedBigInteger('contact_id')->nullable()->index();
            $table->unsignedBigInteger('lead_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_tasks');
    }
};


