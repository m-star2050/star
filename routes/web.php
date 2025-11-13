<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RealEstate\Global\AuthController as GlobalAuthController;
use App\Http\Controllers\RealEstate\Global\DashboardController;
use App\Http\Controllers\RealEstate\Global\TenantController;
use App\Models\SaasTenant;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('realestate/global')->name('global.')->group(function () {
    Route::get('/login', [GlobalAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [GlobalAuthController::class, 'login'])->name('login.attempt');

    Route::middleware('global.admin')->group(function () {
        Route::post('/logout', [GlobalAuthController::class, 'logout'])->name('logout');
        Route::get('/admin', DashboardController::class)->name('admin');

        Route::get('/admin/api/tenants', [TenantController::class, 'list'])->name('tenants.list');
        Route::post('/admin/api/tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::put('/admin/api/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
        Route::patch('/admin/api/tenants/{tenant}/status', [TenantController::class, 'status'])->name('tenants.status');
        Route::delete('/admin/api/tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');
    });
});

Route::get('/realestate/{tenant}', function (string $tenant) {
    if ($tenant === 'global') {
        abort(404);
    }

    $record = SaasTenant::where('slug', $tenant)->firstOrFail();

    return view('realestate.clients.landing', [
        'tenant' => $record,
    ]);
})->middleware('tenant');
