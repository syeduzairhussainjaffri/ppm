<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PpmRecordController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return redirect()->route('ppm.index');
});

Route::get('/ppm/notifications/check', [PpmRecordController::class, 'checkNotifications'])->name('ppm.notifications.check');
Route::get('/ppm/export/csv', [PpmRecordController::class, 'exportCsv'])->name('ppm.export');
Route::resource('ppm', PpmRecordController::class);

Route::post('/customers/quick', [CustomerController::class, 'store'])->name('customers.quick');
Route::resource('customers', CustomerController::class);

Route::post('/products/quick', [ProductController::class, 'store'])->name('products.quick');
Route::resource('products', ProductController::class);
