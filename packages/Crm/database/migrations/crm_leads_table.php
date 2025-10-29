<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('company')->nullable();
            $table->string('source')->nullable()->index();
            $table->enum('stage', ['new', 'contacted', 'qualified', 'won', 'lost'])->default('new')->index();
            $table->unsignedBigInteger('assigned_user_id')->nullable()->index();
            $table->unsignedInteger('lead_score')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_leads');
    }
};


