<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Produk') }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('products.edit', $product) }}"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 transition">
                    Edit Produk
                </a>
                <a href="{{ route('products.index') }}"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200 transition">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 space-y-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Kartu Info Produk --}}
            <x-card>
                <div class="flex items-start justify-between border-b border-gray-200 pb-4 mb-6">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <span class="font-mono font-medium text-gray-700">{{ $product->code }}</span>
                            &middot;
                            <span>Kategori: <strong>{{ $product->category?->name ?? 'Tanpa Kategori' }}</strong></span>
                        </p>
                    </div>
                    <div>
                        <x-badge :status="$product->stock_status" />
                    </div>
                </div>

                {{-- Grid Statistik Ringkas --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-4 text-center border border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Harga Satuan</p>
                        <p class="text-xl font-bold text-gray-900 mt-1">{{ $product->formatted_price }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center border border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Stok Saat Ini</p>
                        <p class="text-xl font-bold text-gray-900 mt-1">{{ $product->stock }} {{ $product->unit }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center border border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Terjual (All-Time)</p>
                        <p class="text-xl font-bold text-indigo-600 mt-1">{{ $totalTerjual }} {{ $product->unit }}</p>
                    </div>
                </div>
            </x-card>

            {{-- Grid Dua Kolom: Riwayat Penjualan & Ranking 5 Hari Tertinggi --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Kolom Kiri: Riwayat Penjualan Terbaru (7 Kolom) --}}
                <div class="lg:col-span-7">
                    <x-card>
                        <div class="border-b border-gray-200 pb-3 mb-4 flex justify-between items-center">
                            <div>
                                <h4 class="font-bold text-gray-800">Riwayat Penjualan Terbaru</h4>
                                <p class="text-xs text-gray-500">Maksimal 10 transaksi penjualan terakhir</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">No. Invoice</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">Tanggal</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-600">Qty</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-600">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($riwayat as $item)
                                        <tr>
                                            <td class="px-3 py-2 font-mono text-xs font-semibold text-gray-900">
                                                {{ $item->transaction?->invoice_number ?? '-' }}
                                            </td>
                                            <td class="px-3 py-2 text-xs text-gray-600">
                                                {{ $item->transaction?->created_at?->format('d M Y H:i') ?? '-' }}
                                            </td>
                                            <td class="px-3 py-2 text-right font-medium text-gray-900">
                                                {{ $item->qty }} {{ $product->unit }}
                                            </td>
                                            <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-3 py-8 text-center text-gray-400">
                                                Belum pernah terjual.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-card>
                </div>

                {{-- Kolom Kanan: 5 Hari dengan Penjualan Tertinggi (5 Kolom) --}}
                <div class="lg:col-span-5">
                    <x-card>
                        <div class="border-b border-gray-200 pb-3 mb-4">
                            <h4 class="font-bold text-gray-800">5 Hari Penjualan Tertinggi</h4>
                            <p class="text-xs text-gray-500">Top 5 tanggal dengan kuantitas terjual terbanyak</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-center font-medium text-gray-600">#</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">Tanggal Transaksi</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-600">Total Terjual</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($topSalesDays as $index => $day)
                                        <tr>
                                            <td class="px-3 py-2 text-center font-bold text-xs text-gray-500">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-3 py-2 text-gray-800 font-medium">
                                                {{ \Carbon\Carbon::parse($day->sale_date)->format('d M Y') }}
                                            </td>
                                            <td class="px-3 py-2 text-right font-bold text-indigo-600">
                                                {{ $day->total_qty }} {{ $product->unit }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-3 py-8 text-center text-gray-400">
                                                Belum ada data penjualan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-card>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
