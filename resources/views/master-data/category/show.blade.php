<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Kategori') }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('categories.edit', $category) }}"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 transition">
                    Edit Kategori
                </a>
                <a href="{{ route('categories.index') }}"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200 transition">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 space-y-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Informasi Header Kategori --}}
            <x-card>
                <div class="border-b border-gray-200 pb-4 mb-4">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $category->name }}</h3>
                    <p class="text-sm text-gray-600 mt-1">{{ $category->description ?: 'Tidak ada deskripsi kategori.' }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4 text-center border border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah Produk Terdaftar</p>
                        <p class="text-xl font-bold text-gray-900 mt-1">{{ $category->products->count() }} Jenis Produk</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center border border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Kuantitas Stok</p>
                        <p class="text-xl font-bold text-indigo-600 mt-1">{{ number_format($totalStok, 0, ',', '.') }} Item</p>
                    </div>
                </div>
            </x-card>

            {{-- Daftar Produk dalam Kategori --}}
            <x-card>
                <div class="border-b border-gray-200 pb-3 mb-4">
                    <h4 class="font-bold text-gray-800">Daftar Produk dalam Kategori</h4>
                    <p class="text-xs text-gray-500">Seluruh produk yang dikelompokkan ke dalam kategori ini</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-600">Kode</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600">Nama Produk</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600">Harga</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600">Stok</th>
                                <th class="px-4 py-2 text-center font-medium text-gray-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($category->products as $product)
                                <tr>
                                    <td class="px-4 py-2 font-mono font-medium text-gray-900">{{ $product->code }}</td>
                                    <td class="px-4 py-2 font-medium text-gray-900">{{ $product->name }}</td>
                                    <td class="px-4 py-2 text-gray-700">{{ $product->formatted_price }}</td>
                                    <td class="px-4 py-2">
                                        <div class="flex items-center gap-2">
                                            <span>{{ $product->stock }} {{ $product->unit }}</span>
                                            <x-badge :status="$product->stock_status" />
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 text-center space-x-2">
                                        <a href="{{ route('products.show', $product) }}"
                                            class="text-blue-600 hover:text-blue-900 font-medium">
                                            Detail
                                        </a>
                                        <a href="{{ route('products.edit', $product) }}"
                                            class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                        Belum ada produk dalam kategori ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>

