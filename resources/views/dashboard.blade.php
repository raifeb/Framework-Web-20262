<x-app-layout>
    {{-- Header Dashboard --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Kartu 1: Sambutan Pengguna --}}
            <x-card>
                <h3 class="text-lg font-semibold mb-2">Ringkasan Hari Ini</h3>
                <p class="text-gray-600">Selamat datang, {{ auth()->user()->name }}.</p>
            </x-card>

            {{-- Kartu 2: Demonstrasi Komponen Badge Stok Produk --}}
            <x-card>
                <h3 class="text-lg font-semibold mb-3">Status Stok Produk</h3>
                <div class="flex items-center space-x-3">
                    <x-badge status="aman" />
                    <x-badge status="menipis" />
                    <x-badge status="habis" />
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>