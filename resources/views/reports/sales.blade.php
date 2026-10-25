<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Penjualan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Ringkasan Laporan --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-card>
                    <p class="text-sm font-medium text-gray-500">Total Pendapatan</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">
                        Rp {{ number_format($totalSales, 0, ',', '.') }}
                    </p>
                </x-card>

                <x-card>
                    <p class="text-sm font-medium text-gray-500">Total Transaksi Selesai</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">
                        {{ number_format($totalTransactions, 0, ',', '.') }} Transaksi
                    </p>
                </x-card>
            </div>

            {{-- Filter Tanggal & Data Penjualan --}}
            <x-card>
                <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-gray-200 pb-4 mb-6 gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Daftar Transaksi</h3>
                        <p class="text-sm text-gray-600">Rincian seluruh transaksi kasir yang tercatat di sistem.</p>
                    </div>

                    <form action="{{ route('report.sales') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        <div>
                            <input type="date" name="start_date" value="{{ $startDate ?? '' }}"
                                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Dari Tanggal">
                        </div>
                        <span class="text-gray-400 text-sm">s/d</span>
                        <div>
                            <input type="date" name="end_date" value="{{ $endDate ?? '' }}"
                                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Sampai Tanggal">
                        </div>
                        <button type="submit"
                            style="background-color: #800020 !important; color: #ffffff !important;"
                            class="px-4 py-2 rounded-md text-sm font-medium hover:opacity-90 transition">
                            Filter
                        </button>
                        @if ($startDate || $endDate)
                            <a href="{{ route('report.sales') }}"
                                class="px-3 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-300 transition">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-700">#</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-700">No. Invoice</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-700">Waktu</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-700">Kasir</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-700">Item Produk</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-700">Total Belanja</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-700">Bayar / Kembali</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($transactions as $index => $transaction)
                                <tr>
                                    <td class="px-4 py-3 text-gray-500">
                                        {{ $transactions->firstItem() + $index }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-gray-900">
                                        {{ $transaction->invoice_number }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $transaction->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $transaction->user->name ?? 'Kasir #' . $transaction->user_id }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @foreach ($transaction->details as $detail)
                                                <span class="inline-block bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs">
                                                    {{ $detail->product->name ?? 'Produk Dihapus' }} ({{ $detail->qty }}x)
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                        Rp {{ number_format($transaction->total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-xs text-gray-600">
                                        <div>Rp {{ number_format($transaction->pay, 0, ',', '.') }}</div>
                                        <div class="text-green-600 font-medium">Kembali: Rp {{ number_format($transaction->change, 0, ',', '.') }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada data transaksi yang sesuai filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($transactions->hasPages())
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </x-card>

        </div>
    </div>
</x-app-layout>

