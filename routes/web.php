<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
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
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    /*
    |----------------------------------------------------------------------
    | Role: Admin Only
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);

        // Laporan Penjualan (Khusus Admin)
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('report.sales');

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
    Route::middleware('role:admin,kasir')->group(function () {
        Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
        Route::post('/pos', [POSController::class, 'store'])->name('pos.store');
        Route::get('/pos/history', [POSController::class, 'history'])->name('pos.history');
    });
});