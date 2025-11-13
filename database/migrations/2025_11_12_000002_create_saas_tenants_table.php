<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->nullable()->constrained('saas_plans');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->unique();
            $table->string('database')->unique();
            $table->string('path')->unique();
            $table->string('status')->default('pending');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->json('limits')->nullable();
            $table->json('settings')->nullable();
            $table->json('billing')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_tenants');
    }
};

