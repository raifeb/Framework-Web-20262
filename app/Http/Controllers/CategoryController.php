<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('products')->latest()->get();
        return view('master-data.category.index', compact('categories'));
    }

    public function edit(Category $category): View
    {
        return view('master-data.category.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:categories,name,' . $category->id],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki produk terkait.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
