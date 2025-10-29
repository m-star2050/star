<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('original_name');
            $table->string('mime', 191)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('path');
            $table->enum('linked_type', ['contact', 'lead', 'deal'])->nullable()->index();
            $table->unsignedBigInteger('linked_id')->nullable()->index();
            $table->unsignedBigInteger('uploaded_by')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_files');
    }
};


