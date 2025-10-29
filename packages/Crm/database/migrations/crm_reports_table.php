<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_reports', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->json('filters')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_reports');
    }
};


