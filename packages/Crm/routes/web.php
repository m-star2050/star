<?php 

use Illuminate\Support\Facades\Route;

Route::prefix('crm')->group(function () {
    Route::get('check', function(){
        echo "Check";
    });
    Route::middleware(['web'])->group(function () {
      
    });

  
});

Route::get('/crm', function () {
    return 'CRM Module Connected!';
});




