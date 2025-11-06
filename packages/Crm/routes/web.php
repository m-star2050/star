<?php 

use Illuminate\Support\Facades\Route;
use Packages\Crm\Http\Controllers\ContactController;
use Packages\Crm\Http\Controllers\LeadController;
use Packages\Crm\Http\Controllers\TaskController;
use Packages\Crm\Http\Controllers\PipelineController;
use Packages\Crm\Http\Controllers\ReportsController;
use Packages\Crm\Http\Controllers\FilesController;

Route::prefix('crm')->group(function () {
    Route::get('check', function(){
        echo "Check";
    });
    Route::middleware(['web'])->group(function () {
        Route::name('crm.')->group(function () {
            Route::resource('contacts', ContactController::class)->only(['index','store','update','destroy']);
            Route::get('contacts/datatable', [ContactController::class, 'datatable'])->name('contacts.datatable');
            Route::post('contacts/{contact}/inline', [ContactController::class, 'inline'])->name('contacts.inline');
            Route::post('contacts/{id}/restore', [ContactController::class, 'restore'])->name('contacts.restore');
            Route::post('contacts/bulk-delete', [ContactController::class, 'bulkDelete'])->name('contacts.bulk-delete');
            Route::get('contacts-export', [ContactController::class, 'export'])->name('contacts.export');

            Route::resource('leads', LeadController::class)->only(['index','store','update','destroy']);
            Route::get('leads/datatable', [LeadController::class, 'datatable'])->name('leads.datatable');
            Route::post('leads/{lead}/stage', [LeadController::class, 'inlineStage'])->name('leads.stage');
            Route::post('leads/{lead}/convert-to-contact', [LeadController::class, 'convertToContact'])->name('leads.convert-to-contact');
            Route::post('leads/{lead}/convert-to-deal', [LeadController::class, 'convertToDeal'])->name('leads.convert-to-deal');
            Route::post('leads/{id}/restore', [LeadController::class, 'restore'])->name('leads.restore');
            Route::post('leads/bulk-delete', [LeadController::class, 'bulkDelete'])->name('leads.bulk-delete');
            Route::get('leads-export', [LeadController::class, 'export'])->name('leads.export');

            Route::resource('tasks', TaskController::class)->only(['index','store','update','destroy']);
            Route::get('tasks/datatable', [TaskController::class, 'datatable'])->name('tasks.datatable');
            Route::post('tasks/{task}/toggle-status', [TaskController::class, 'toggleStatus'])->name('tasks.toggle-status');
            Route::post('tasks/{id}/restore', [TaskController::class, 'restore'])->name('tasks.restore');
            Route::post('tasks/bulk-delete', [TaskController::class, 'bulkDelete'])->name('tasks.bulk-delete');
            Route::get('tasks-export', [TaskController::class, 'export'])->name('tasks.export');

            Route::get('pipeline', [PipelineController::class, 'index'])->name('pipeline.index');
            Route::get('pipeline/kanban', [PipelineController::class, 'kanban'])->name('pipeline.kanban');
            Route::get('pipeline/datatable', [PipelineController::class, 'datatable'])->name('pipeline.datatable');
            Route::get('pipeline/kanban-data', [PipelineController::class, 'kanbanData'])->name('pipeline.kanban-data');
            Route::post('pipeline', [PipelineController::class, 'store'])->name('pipeline.store');
            Route::put('pipeline/{pipeline}', [PipelineController::class, 'update'])->name('pipeline.update');
            Route::delete('pipeline/{pipeline}', [PipelineController::class, 'destroy'])->name('pipeline.destroy');
            Route::post('pipeline/{pipeline}/update-stage', [PipelineController::class, 'updateStage'])->name('pipeline.update-stage');
            Route::post('pipeline/{id}/restore', [PipelineController::class, 'restore'])->name('pipeline.restore');
            Route::post('pipeline/bulk-delete', [PipelineController::class, 'bulkDelete'])->name('pipeline.bulk-delete');
            Route::get('pipeline-export', [PipelineController::class, 'export'])->name('pipeline.export');

            Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
            Route::get('reports/dashboard-data', [ReportsController::class, 'dashboardData'])->name('reports.dashboard-data');
            Route::get('reports/chart-data', [ReportsController::class, 'chartData'])->name('reports.chart-data');
            Route::get('reports/datatable', [ReportsController::class, 'datatable'])->name('reports.datatable');
            Route::get('reports-export', [ReportsController::class, 'export'])->name('reports.export');

            Route::get('files', [FilesController::class, 'index'])->name('files.index');
            Route::get('files/datatable', [FilesController::class, 'datatable'])->name('files.datatable');
            Route::post('files', [FilesController::class, 'store'])->name('files.store');
            Route::get('files/{file}/download', [FilesController::class, 'download'])->name('files.download');
            Route::get('files/{file}/preview', [FilesController::class, 'preview'])->name('files.preview');
            Route::delete('files/{file}', [FilesController::class, 'destroy'])->name('files.destroy');
            Route::post('files/bulk-delete', [FilesController::class, 'bulkDelete'])->name('files.bulk-delete');

            // API endpoints for dropdowns
            Route::get('api/users', function() {
                $users = \App\Models\User::select('id', 'name', 'email')->orderBy('name')->get();
                return response()->json([
                    'success' => true,
                    'data' => $users
                ]);
            })->name('api.users');
            
            Route::get('api/contacts', function() {
                $contacts = \Packages\Crm\Models\Contact::select('id', 'name', 'email', 'company')->orderBy('name')->get();
                return response()->json([
                    'success' => true,
                    'data' => $contacts
                ]);
            })->name('api.contacts');
            
            Route::get('api/leads', function() {
                $leads = \Packages\Crm\Models\Lead::select('id', 'name', 'email', 'company')->orderBy('name')->get();
                return response()->json([
                    'success' => true,
                    'data' => $leads
                ]);
            })->name('api.leads');
        });
    });

  
});

Route::get('/crm', function () {
    return 'CRM Module Connected!';
});





