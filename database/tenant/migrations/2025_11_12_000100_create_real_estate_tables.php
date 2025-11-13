<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('real_properties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('broker_id')->nullable();
            $table->string('title')->nullable();
            $table->string('reference')->nullable();
            $table->string('status')->default('draft');
            $table->string('listing_type')->nullable();
            $table->unsignedInteger('bedrooms')->nullable();
            $table->unsignedInteger('bathrooms')->nullable();
            $table->decimal('price', 14, 2)->default(0);
            $table->decimal('area', 10, 2)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->json('attributes')->nullable();
            $table->json('location')->nullable();
            $table->json('media')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('real_property_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id');
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('real_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('real_amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('real_agents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('license_number')->nullable();
            $table->string('status')->default('pending');
            $table->json('profile')->nullable();
            $table->timestamps();
        });

        Schema::create('real_brokers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('license_number')->nullable();
            $table->string('status')->default('pending');
            $table->json('profile')->nullable();
            $table->timestamps();
        });

        Schema::create('real_owners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->json('documents')->nullable();
            $table->timestamps();
        });

        Schema::create('real_tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('lease_start')->nullable();
            $table->date('lease_end')->nullable();
            $table->json('preferences')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('real_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('category')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('expense_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('real_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('broker_id')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('rate', 5, 2)->default(0);
            $table->string('status')->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('real_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('type')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('terms')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('real_staff', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('role')->nullable();
            $table->string('status')->default('active');
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        Schema::create('real_favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('property_id');
            $table->timestamps();
        });

        Schema::create('real_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('broker_id')->nullable();
            $table->unsignedBigInteger('property_id')->nullable();
            $table->unsignedTinyInteger('rating')->default(0);
            $table->text('feedback')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('real_reviews');
        Schema::dropIfExists('real_favorites');
        Schema::dropIfExists('real_staff');
        Schema::dropIfExists('real_contracts');
        Schema::dropIfExists('real_commissions');
        Schema::dropIfExists('real_expenses');
        Schema::dropIfExists('real_tenants');
        Schema::dropIfExists('real_owners');
        Schema::dropIfExists('real_brokers');
        Schema::dropIfExists('real_agents');
        Schema::dropIfExists('real_amenities');
        Schema::dropIfExists('real_categories');
        Schema::dropIfExists('real_property_images');
        Schema::dropIfExists('real_properties');
    }
};

