<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Otentikasi
        User::create([
            'name' => 'Admin Toko',
            'email' => 'admin@barokah.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Kasir Rina',
            'email' => 'kasir@barokah.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
        ]);

        // 2. Data Master Kategori
        $makanan = Category::create([
            'name' => 'Makanan Ringan',
            'description' => 'Kategori snack dan biskuit',
        ]);

        $minuman = Category::create([
            'name' => 'Minuman',
            'description' => 'Kategori air mineral dan minuman kemasan',
        ]);

        // 3. Data Master Produk
        Product::create([
            'category_id' => $makanan->id,
            'code' => 'BRG001',
            'name' => 'Chitato Sapi Panggang 68g',
            'unit' => 'pcs',
            'price' => 11500,
            'stock' => 50,
        ]);

        Product::create([
            'category_id' => $minuman->id,
            'code' => 'BRG002',
            'name' => 'Le Minerale 600ml',
            'unit' => 'botol',
            'price' => 3500,
            'stock' => 100,
        ]);
    }
}