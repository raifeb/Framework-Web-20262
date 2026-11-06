<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Public & Guest Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Logout (mendukung POST dari tombol form maupun akses direct)
    Route::match(['get', 'post'], '/logout', [LoginController::class, 'destroy'])->name('logout');

    // Dashboard Bersama (Admin & Kasir)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |----------------------------------------------------------------------
    | Role: Admin Only
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['create', 'store']);
        Route::resource('products', ProductController::class);

        // Pertemuan 13: Laporan Penjualan & Export Excel (Khusus Admin)
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('report.sales');
        Route::get('/reports/sales/export', [ReportController::class, 'export'])->name('report.export');
        // Backward-compatible alias
        Route::get('/reports-sales', [ReportController::class, 'sales'])->name('reports.sales');

        // Latihan Praktikum 5: Manajemen Kasir
        Route::get('/users', function () {
            return 'Halaman Manajemen Akun Kasir (Khusus Admin)';
        })->name('users.index');
    });

    /*
    |----------------------------------------------------------------------
    | Role: Kasir & Admin (POS & Transaksi)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin,kasir')->prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::post('/', [POSController::class, 'store'])->name('store');
        Route::get('/history', [POSController::class, 'history'])->name('history');
        Route::get('/{transaction}/struk', [POSController::class, 'struk'])->name('struk');
    });
});