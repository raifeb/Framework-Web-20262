<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Kategori') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Master Kategori</h3>
                        <p class="text-sm text-gray-600">Daftar seluruh kategori produk dalam sistem.</p>
                    </div>
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

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">#</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">Nama Kategori</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700">Deskripsi</th>
                                <th class="px-4 py-2 text-center font-medium text-gray-700">Jumlah Produk</th>
                                <th class="px-4 py-2 text-center font-medium text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($categories as $index => $category)
                                <tr>
                                    <td class="px-4 py-2 text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2 font-medium text-gray-900">{{ $category->name }}</td>
                                    <td class="px-4 py-2 text-gray-600">{{ $category->description ?? '-' }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $category->products_count > 0 ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $category->products_count }} Produk
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-center space-x-2">
                                        <a href="{{ route('categories.edit', $category) }}"
                                            class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            Edit
                                        </a>
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus kategori {{ $category->name }}?')"
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
                                    <td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada data kategori.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
