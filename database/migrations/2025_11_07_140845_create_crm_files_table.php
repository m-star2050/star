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
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('file_path');
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

   
    public function down(): void
    {
        Schema::dropIfExists('crm_files');
    }
};

