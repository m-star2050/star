<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_pipelines')) {
            Schema::create('crm_pipelines', function (Blueprint $table) {
                $table->id();
                $table->string('deal_name');
                $table->enum('stage', ['prospect', 'negotiation', 'proposal', 'closed_won', 'closed_lost'])->default('prospect')->index();
                $table->decimal('value', 15, 2)->default(0);
                $table->unsignedBigInteger('owner_user_id')->nullable()->index();
                $table->date('close_date')->nullable()->index();
                $table->unsignedTinyInteger('probability')->nullable();
                $table->unsignedBigInteger('contact_id')->nullable()->index();
                $table->string('company')->nullable();
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_pipelines');
    }
};

