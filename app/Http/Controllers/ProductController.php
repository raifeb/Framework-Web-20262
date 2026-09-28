<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Menampilkan daftar data produk.
     *
     * @return View|RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        // 1. Ambil data produk beserta relasi kategori (Eager Loading)
        $products = Product::with('category')->latest()->get();

        // 2. Tampilkan view jika tersedia, atau alihkan ke form tambah jika view index belum dibuat
        if (view()->exists('master-data.product.index')) {
            return view('master-data.product.index', compact('products'));
        }

        return redirect()->route('products.create');
    }

    /**
     * Menampilkan formulir untuk menambahkan produk baru.
     *
     * @return View
     */
    public function create(): View
    {
        // 1. Ambil data kategori dengan urutan alfabetis untuk opsi dropdown
        $categories = Category::orderBy('name', 'asc')->get();

        // 2. Render tampilan form tambah produk dengan passing data kategori
        return view('master-data.product.create', compact('categories'));
    }

    /**
     * Menyimpan data produk baru ke dalam database.
     *
     * @param StoreProductRequest $request
     * @return RedirectResponse
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        // 1. Ambil data yang telah tervalidasi dari Form Request
        $validatedData = $request->validated();

        // 2. Simpan record produk baru ke tabel database
        Product::create($validatedData);

        // 3. Alihkan kembali ke form tambah dengan pesan notifikasi sukses
        return redirect()
            ->route('products.create')
            ->with('success', 'Produk berhasil ditambahkan.');
    }
}