<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Produk') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Katalog Produk</h3>
                        <p class="text-sm text-gray-600">Daftar seluruh produk beserta kategorinya.</p>
                    </div>
                    <a href="{{ route('products.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Tambah Produk
                    </a>
                </div>

                {{-- Notifikasi Sukses / Error --}}
                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-md text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-md text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Form Pencarian dan Filter (Pertemuan 10) --}}
                <form method="GET" action="{{ route('products.index') }}" class="mb-6 space-y-3">
                    <div class="flex flex-col md:flex-row gap-3">
                        {{-- Input Pencarian Nama / Kode --}}
                        <div class="flex-1">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari nama atau kode produk..."
                                class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        {{-- Filter Kategori --}}
                        <div class="w-full md:w-48">
                            <select name="category_id" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Semua Kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter Status Stok --}}
                        <div class="w-full md:w-44">
                            <select name="stock_status" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Semua Stok</option>
                                <option value="aman" @selected(request('stock_status') === 'aman')>Stok Aman (> 10)</option>
                                <option value="menipis" @selected(request('stock_status') === 'menipis')>Stok Menipis (1-10)</option>
                                <option value="habis" @selected(request('stock_status') === 'habis')>Stok Habis (0)</option>
                            </select>
                        </div>

                        {{-- Per Page --}}
                        <div class="w-full md:w-32">
                            <select name="per_page" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="10" @selected(request('per_page', 10) == 10)>10 / Hal</option>
                                <option value="25" @selected(request('per_page') == 25)>25 / Hal</option>
                                <option value="50" @selected(request('per_page') == 50)>50 / Hal</option>
                            </select>
                        </div>

                        {{-- Tombol Aksi Filter --}}
                        <div class="flex items-center gap-2">
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 transition">
                                Filter
                            </button>
                            @if (request()->hasAny(['search', 'category_id', 'stock_status', 'per_page']))
                                <a href="{{ route('products.index') }}"
                                    class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200 transition">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                {{-- Tabel Daftar Produk --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">Kode</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">Nama Produk</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">Kategori</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">Harga</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">Stok</th>
                                <th class="px-4 py-2 text-center font-medium text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($products as $product)
                                <tr>
                                    <td class="px-4 py-2 font-mono font-medium text-gray-900">{{ $product->code }}</td>
                                    <td class="px-4 py-2">{{ $product->name }}</td>
                                    <td class="px-4 py-2 text-gray-600">{{ $product->category?->name ?? '-' }}</td>
                                    <td class="px-4 py-2 font-medium">{{ $product->formatted_price }}</td>
                                    <td class="px-4 py-2">
                                        <div class="flex items-center gap-2">
                                            <span>{{ $product->stock }} {{ $product->unit }}</span>
                                            <x-badge :status="$product->stock_status" />
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 text-center space-x-2">
                                        <a href="{{ route('products.edit', $product) }}"
                                            class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            Edit
                                        </a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus produk {{ $product->name }}?')"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-medium">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                        Tidak ada produk yang sesuai dengan kriteria filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Navigasi Pagination --}}
                @if ($products->hasPages())
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        {{ $products->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>
