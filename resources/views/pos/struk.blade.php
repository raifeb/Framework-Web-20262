<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between no-print">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Struk Transaksi') }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('pos.history') }}"
                    class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm font-medium transition">
                    &larr; Riwayat
                </a>
                <a href="{{ route('pos.index') }}"
                    style="background-color: #800020 !important; color: #ffffff !important;"
                    class="px-3 py-1.5 rounded-md text-sm font-medium hover:opacity-90 transition">
                    + Transaksi Baru
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        @media print {
            @page {
                size: 58mm auto;
                margin: 0;
            }
            nav, header, .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 58mm !important;
            }
            #receipt-area {
                box-shadow: none !important;
                border: none !important;
                max-width: 58mm !important;
                width: 58mm !important;
                margin: 0 auto !important;
                padding: 6px !important;
                font-size: 11px !important;
                line-height: 1.2 !important;
            }
        }
    </style>

    <div class="py-8">
        <div class="max-w-md mx-auto sm:px-6 space-y-4">

            {{-- Flash Message --}}
            @if (session('success'))
                <div class="no-print p-4 bg-green-100 border border-green-200 text-green-800 rounded-lg text-sm font-medium flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-900">&times;</button>
                </div>
            @endif

            {{-- Receipt Container --}}
            <div id="receipt-area" class="bg-white p-6 rounded-lg shadow border border-gray-200 font-mono text-sm">
                {{-- Store Info --}}
                <div class="text-center pb-3 border-b border-dashed border-gray-300">
                    <h3 class="font-bold text-lg text-gray-900 tracking-wider">BAROKAH MART</h3>
                    <p class="text-xs text-gray-500">Jl. Ringroad Utara, Sleman, Yogyakarta</p>
                    <p class="text-xs text-gray-500">Telp: 0812-3456-7890</p>
                </div>

                {{-- Transaction Info --}}
                <div class="py-3 border-b border-dashed border-gray-300 text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-gray-500">No. Invoice:</span>
                        <span class="font-semibold text-gray-800">{{ $transaction->invoice_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tanggal:</span>
                        <span class="text-gray-800">{{ $transaction->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Kasir:</span>
                        <span class="text-gray-800">{{ $transaction->user->name ?? 'Kasir' }}</span>
                    </div>
                </div>

                {{-- Item List --}}
                <div class="py-3 border-b border-dashed border-gray-300 space-y-2">
                    @foreach ($transaction->details as $detail)
                        <div>
                            <div class="font-medium text-gray-800">{{ $detail->product->name ?? 'Produk' }}</div>
                            <div class="flex justify-between text-xs text-gray-600 pl-2">
                                <span>{{ $detail->qty }} &times; Rp{{ number_format($detail->price, 0, ',', '.') }}</span>
                                <span class="font-semibold text-gray-900">Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Payment Summary --}}
                <div class="py-3 space-y-1.5 text-xs">
                    <div class="flex justify-between text-sm font-bold text-gray-900">
                        <span>TOTAL</span>
                        <span>Rp{{ number_format($transaction->total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-gray-700">
                        <span>TUNAI / BAYAR</span>
                        <span>Rp{{ number_format($transaction->pay, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-semibold text-green-700">
                        <span>KEMBALIAN</span>
                        <span>Rp{{ number_format($transaction->change, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Footer / Note --}}
                <div class="pt-3 border-t border-dashed border-gray-300 text-center text-xs text-gray-500 space-y-0.5">
                    <p>Terima kasih atas kunjungan Anda</p>
                    <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
                </div>
            </div>

            {{-- Action Buttons (Screen only) --}}
            <div class="no-print space-y-2">
                <button type="button" onclick="window.print()"
                    style="background-color: #800020 !important; color: #ffffff !important;"
                    class="w-full py-2.5 rounded-md font-semibold text-sm hover:opacity-90 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak Struk (Print)
                </button>

                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('pos.index') }}"
                        class="text-center py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm font-medium transition">
                        + Transaksi Baru
                    </a>
                    <a href="{{ route('pos.history') }}"
                        class="text-center py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm font-medium transition">
                        Riwayat Transaksi
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
