<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;

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
        Route::resource('categories', CategoryController::class)->except(['create', 'store']);
        Route::resource('products', ProductController::class);

        // Laporan Penjualan (Khusus Admin)
        Route::get('/reports-sales', function () {
            return view('reports.sales');
        })->name('reports.sales');

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
