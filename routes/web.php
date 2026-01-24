<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockEntryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Guest routes (not authenticated)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gerant only routes
    Route::middleware('role:gerant')->prefix('gerant')->name('gerant.')->group(function () {
        // Products CRUD
        Route::resource('products', ProductController::class);
        // Categories CRUD (sans show car pas necessaire)
        Route::resource('categories', CategoryController::class)->except(['show']);
        // Suppliers CRUD
        Route::resource('suppliers', SupplierController::class);
        // Stock Entries CRUD
        Route::resource('stock-entries', StockEntryController::class);
        // Users CRUD
        Route::resource('users', UserController::class);
        // Stock Adjustments (index, create, store, show only - no edit/delete for traceability)
        Route::resource('stock-adjustments', StockAdjustmentController::class)->only(['index', 'create', 'store', 'show']);

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/low-stock', [ReportController::class, 'lowStock'])->name('low-stock');
            Route::get('/stock-entries-by-supplier', [ReportController::class, 'stockEntriesBySupplier'])->name('stock-entries-by-supplier');
            Route::get('/stock-adjustments', [ReportController::class, 'stockAdjustments'])->name('stock-adjustments');
        });

        // Sales management (view, cancel)
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('/', [SaleController::class, 'index'])->name('index');
            Route::get('/cancelled', [SaleController::class, 'cancelledHistory'])->name('cancelled');
            Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
            Route::get('/{sale}/cancel', [SaleController::class, 'cancelForm'])->name('cancel.form');
            Route::post('/{sale}/cancel', [SaleController::class, 'cancel'])->name('cancel');
        });
    });

    // Vendeur routes (accessible by both vendeur and gerant)
    Route::middleware('role:vendeur,gerant')->prefix('ventes')->name('ventes.')->group(function () {
        // Future routes: sales, customers
    });
});
