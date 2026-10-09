<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PpmRecordController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;

use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return redirect()->route('ppm.index');
});

// Reports & Analytics Routes
Route::prefix('reports')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/customer-wise', [ReportController::class, 'customerReport'])->name('reports.customer');
    Route::get('/product-wise', [ReportController::class, 'productReport'])->name('reports.product');
    Route::get('/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export');
    Route::get('/print', [ReportController::class, 'printReport'])->name('reports.print');
});

Route::get('/ppm/notifications/check', [PpmRecordController::class, 'checkNotifications'])->name('ppm.notifications.check');
Route::get('/ppm/export/csv', [PpmRecordController::class, 'exportCsv'])->name('ppm.export');
Route::resource('ppm', PpmRecordController::class);

Route::post('/customers/quick', [CustomerController::class, 'store'])->name('customers.quick');
Route::resource('customers', CustomerController::class);

Route::post('/products/quick', [ProductController::class, 'store'])->name('products.quick');
Route::resource('products', ProductController::class);
