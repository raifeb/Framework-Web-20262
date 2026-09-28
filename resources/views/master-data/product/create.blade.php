<x-app-layout>
    {{-- Header Halaman --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Produk') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                {{-- Judul dan Keterangan Formulir --}}
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-lg font-bold text-gray-800">Formulir Tambah Produk Baru</h3>
                    <p class="text-sm text-gray-600">Lengkapi formulir di bawah ini untuk menambahkan produk ke katalog sistem.</p>
                </div>

                {{-- Notifikasi Sukses --}}
                @if (session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-md text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Form Penginputan Data Produk --}}
                <form action="{{ route('products.store') }}" method="POST" class="space-y-6">
                    @csrf

                    {{-- 1. Dropdown Pilihan Kategori --}}
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Kategori</label>
                        <select id="category_id" name="category_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="" disabled selected>-- Pilih Kategori Produk --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 2. Input Kode Produk --}}
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700">Kode Produk</label>
                        <input type="text" id="code" name="code" value="{{ old('code') }}"
                            placeholder="Contoh: BRG003"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 3. Input Nama Produk --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Produk</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                            placeholder="Contoh: Teh Pucuk Harum 350ml"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 4. Grid Input Satuan, Harga, dan Stok Awal --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Satuan Produk --}}
                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700">Satuan</label>
                            <input type="text" id="unit" name="unit" value="{{ old('unit', 'pcs') }}"
                                placeholder="pcs / botol / renceng"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('unit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Harga Produk --}}
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700">Harga (Rp)</label>
                            <input type="number" id="price" name="price" value="{{ old('price') }}" min="0"
                                placeholder="Contoh: 5000"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Stok Awal Produk --}}
                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700">Stok Awal</label>
                            <input type="number" id="stock" name="stock" value="{{ old('stock', 0) }}" min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('stock')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- 5. Tombol Aksi (Batal & Simpan) --}}
                    <div class="pt-4 flex items-center justify-end space-x-3 border-t border-gray-200">
                        <a href="{{ route('dashboard') }}"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Batal
                        </a>
                        <button type="submit"
                            class="inline-flex justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Simpan Produk
                        </button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>