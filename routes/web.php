<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
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
    });

    // Vendeur routes (accessible by both vendeur and gerant)
    Route::middleware('role:vendeur,gerant')->prefix('ventes')->name('ventes.')->group(function () {
        // Future routes: sales, customers
    });
});
