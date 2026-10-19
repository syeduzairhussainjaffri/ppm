<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PpmRecordController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Middleware\RoleMiddleware;

/*
|--------------------------------------------------------------------------
| Guest Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Application Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', RoleMiddleware::class])->group(function () {

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/', function () {
        return redirect()->route('ppm.index');
    });

    // Multi-Branch Active Switcher (for users with multi-branch access)
    Route::post('/branches/switch', [BranchController::class, 'switchBranch'])->name('branches.switch');

    // Reports & Analytics Routes
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/customer-wise', [ReportController::class, 'customerReport'])->name('reports.customer');
        Route::get('/product-wise', [ReportController::class, 'productReport'])->name('reports.product');
        Route::get('/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export');
        Route::get('/print', [ReportController::class, 'printReport'])->name('reports.print');
    });

    // PPM Field Operations
    Route::get('/ppm/notifications/check', [PpmRecordController::class, 'checkNotifications'])->name('ppm.notifications.check');
    Route::get('/ppm/export/csv', [PpmRecordController::class, 'exportCsv'])->name('ppm.export');
    Route::post('/ppm/import/csv', [PpmRecordController::class, 'importCsv'])->name('ppm.import');
    Route::resource('ppm', PpmRecordController::class);

    // Dynamic Master Data: Customers
    Route::get('/customers/export/csv', [CustomerController::class, 'exportCsv'])->name('customers.export');
    Route::post('/customers/import/csv', [CustomerController::class, 'importCsv'])->name('customers.import');
    Route::post('/customers/quick', [CustomerController::class, 'store'])->name('customers.quick');
    Route::resource('customers', CustomerController::class);

    // Dynamic Master Data: Products
    Route::get('/products/export/csv', [ProductController::class, 'exportCsv'])->name('products.export');
    Route::post('/products/import/csv', [ProductController::class, 'importCsv'])->name('products.import');
    Route::post('/products/quick', [ProductController::class, 'store'])->name('products.quick');
    Route::resource('products', ProductController::class);

    // Unified User, Role, Permission & Branch Management Routes
    Route::middleware('can:manage-users')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Dynamic Role Management Routes
        Route::post('/roles', [UserController::class, 'storeRole'])->name('roles.store');
        Route::put('/roles/{role}', [UserController::class, 'updateRole'])->name('roles.update');
        Route::delete('/roles/{role}', [UserController::class, 'destroyRole'])->name('roles.destroy');

        // Dynamic Permission Routes
        Route::post('/permissions', [UserController::class, 'storePermission'])->name('permissions.store');
        Route::delete('/permissions/{permission}', [UserController::class, 'destroyPermission'])->name('permissions.destroy');

        // Dynamic Branch Management Routes
        Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
        Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::post('/branches/{branch}/toggle', [BranchController::class, 'toggleStatus'])->name('branches.toggle');
        Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
    });

});
