<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\POSController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
// use App\Http\Controllers\DashboardController; // Aktifkan jika ada filenya

// Rute Pengalihan Awal
Route::get('/', function () {
    return redirect()->route('pos.index');
});

// Rute Autentikasi
Route::get('/login', [LoginController::class, 'create'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [LoginController::class, 'store'])
    ->middleware('guest')
    ->name('login.store');

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Rute Dashboard (Bisa diakses Admin & Kasir yang sudah login)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// Rute Khusus Admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('report.sales');

    // Latihan 1: Rute Kelola Akun Kasir
    Route::get('/users', function () {
        return 'Halaman Manajemen Akun Kasir (Khusus Admin)';
    })->name('users.index');
});

// Rute Kasir dan Admin (POS)
Route::middleware(['auth', 'role:admin,kasir'])->group(function () {
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::post('/pos', [POSController::class, 'store'])->name('pos.store');
});