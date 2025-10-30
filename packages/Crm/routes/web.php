<?php 

use Illuminate\Support\Facades\Route;
use Packages\Crm\Http\Controllers\ContactController;

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
        });
    });

  
});

Route::get('/crm', function () {
    return 'CRM Module Connected!';
});




