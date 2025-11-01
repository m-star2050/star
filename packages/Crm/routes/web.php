<?php 

use Illuminate\Support\Facades\Route;
use Packages\Crm\Http\Controllers\ContactController;
use Packages\Crm\Http\Controllers\LeadController;
use Packages\Crm\Http\Controllers\TaskController;
use Packages\Crm\Http\Controllers\PipelineController;

Route::prefix('crm')->group(function () {
    Route::get('check', function(){
        echo "Check";
    });
    Route::middleware(['web'])->group(function () {
        Route::name('crm.')->group(function () {
            Route::resource('contacts', ContactController::class)->only(['index','store','update','destroy']);
            Route::post('contacts/{contact}/inline', [ContactController::class, 'inline'])->name('contacts.inline');
            Route::post('contacts/{id}/restore', [ContactController::class, 'restore'])->name('contacts.restore');
            Route::post('contacts/bulk-delete', [ContactController::class, 'bulkDelete'])->name('contacts.bulk-delete');
            Route::get('contacts-export', [ContactController::class, 'export'])->name('contacts.export');

            Route::resource('leads', LeadController::class)->only(['index','store','update','destroy']);
            Route::post('leads/{lead}/stage', [LeadController::class, 'inlineStage'])->name('leads.stage');
            Route::post('leads/{lead}/convert-to-contact', [LeadController::class, 'convertToContact'])->name('leads.convert-to-contact');
            Route::post('leads/{lead}/convert-to-deal', [LeadController::class, 'convertToDeal'])->name('leads.convert-to-deal');
            Route::post('leads/{id}/restore', [LeadController::class, 'restore'])->name('leads.restore');
            Route::post('leads/bulk-delete', [LeadController::class, 'bulkDelete'])->name('leads.bulk-delete');
            Route::get('leads-export', [LeadController::class, 'export'])->name('leads.export');

            Route::resource('tasks', TaskController::class)->only(['index','store','update','destroy']);
            Route::post('tasks/{task}/toggle-status', [TaskController::class, 'toggleStatus'])->name('tasks.toggle-status');
            Route::post('tasks/{id}/restore', [TaskController::class, 'restore'])->name('tasks.restore');
            Route::post('tasks/bulk-delete', [TaskController::class, 'bulkDelete'])->name('tasks.bulk-delete');
            Route::get('tasks-export', [TaskController::class, 'export'])->name('tasks.export');

            Route::get('pipeline', [PipelineController::class, 'index'])->name('pipeline.index');
            Route::get('pipeline/kanban', [PipelineController::class, 'kanban'])->name('pipeline.kanban');
            Route::post('pipeline', [PipelineController::class, 'store'])->name('pipeline.store');
            Route::put('pipeline/{pipeline}', [PipelineController::class, 'update'])->name('pipeline.update');
            Route::delete('pipeline/{pipeline}', [PipelineController::class, 'destroy'])->name('pipeline.destroy');
            Route::post('pipeline/{pipeline}/update-stage', [PipelineController::class, 'updateStage'])->name('pipeline.update-stage');
            Route::post('pipeline/{id}/restore', [PipelineController::class, 'restore'])->name('pipeline.restore');
            Route::post('pipeline/bulk-delete', [PipelineController::class, 'bulkDelete'])->name('pipeline.bulk-delete');
            Route::get('pipeline-export', [PipelineController::class, 'export'])->name('pipeline.export');
        });
    });

  
});

Route::get('/crm', function () {
    return 'CRM Module Connected!';
});





