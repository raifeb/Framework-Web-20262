<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Riwayat Transaksi') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    @if (auth()->user()->role === 'kasir')
                        Riwayat transaksi penjualan yang Anda proses.
                    @else
                        Semua transaksi kasir yang tercatat di sistem.
                    @endif
                </p>
            </div>
            <a href="{{ route('pos.index') }}"
                style="background-color: #800020 !important; color: #ffffff !important;"
                class="px-4 py-2 rounded-md text-sm font-medium hover:opacity-90 transition">
                + Transaksi Baru
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-card>
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
                                <th class="px-4 py-3 text-right font-medium text-gray-700">Bayar / Kembalian</th>
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
                                                    {{ $detail->product->name ?? 'Produk' }} ({{ $detail->qty }}x)
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
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                        Belum ada riwayat transaksi.
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

