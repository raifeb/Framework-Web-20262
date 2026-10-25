<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $keyword = $request->query('search');
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('code', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        if ($request->filled('stock_status')) {
            $status = $request->query('stock_status');
            if ($status === 'habis') {
                $query->where('stock', '<=', 0);
            } elseif ($status === 'menipis') {
                $query->where('stock', '>', 0)->where('stock', '<=', 10);
            } elseif ($status === 'aman') {
                $query->where('stock', '>', 10);
            }
        }

        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50])
            ? (int) $request->query('per_page')
            : 10;

        $products = $query->latest()->paginate($perPage)->withQueryString();
        $categories = Category::orderBy('name', 'asc')->get();

        return view('master-data.product.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name', 'asc')->get();
        return view('master-data.product.create', compact('categories'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        if ($validatedData['price'] > 0 && $validatedData['stock'] >= 0) {
            $validatedData['code'] = strtoupper(trim($validatedData['code']));
            $validatedData['unit'] = strtolower(trim($validatedData['unit']));

            Product::create($validatedData);

            return redirect()
                ->route('products.create')
                ->with('success', 'Produk berhasil ditambahkan.');
        }

        abort(422);
    }

    private function formatProductData(array $data): array
    {
        $data['code'] = strtoupper(trim($data['code']));
        $data['unit'] = strtolower(trim($data['unit']));

        return $data;
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name', 'asc')->get();
        return view('master-data.product.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
