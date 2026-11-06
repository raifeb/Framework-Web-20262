<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Dashboard</h2></x-slot>

    <div class="py-8 space-y-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-card>
                <p class="text-xs text-gray-500">Omzet Hari Ini</p>
                <p class="text-xl font-semibold">Rp{{ number_format($omzetHariIni, 0, ',', '.') }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-gray-500">Omzet Kemarin</p>
                <p class="text-xl font-semibold">Rp{{ number_format($omzetKemarin, 0, ',', '.') }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-gray-500">Transaksi Hari Ini</p>
                <p class="text-xl font-semibold">{{ $transaksiHariIni }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-gray-500">Produk Stok Menipis</p>
                <p class="text-xl font-semibold text-yellow-600">{{ $produkMenipis }}</p>
            </x-card>
        </div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-card class="lg:col-span-2">
                <h3 class="font-semibold mb-3">Tren Penjualan 7 Hari Terakhir</h3>
                <canvas id="chartTren" height="120"></canvas>
            </x-card>

            <x-card>
                <h3 class="font-semibold mb-3">Produk Terlaris</h3>
                <ol class="list-decimal list-inside space-y-1 text-sm">
                    @forelse ($produkTerlaris as $produk)
                        <li>{{ $produk->name }} <span class="text-gray-500">({{ $produk->total_qty }} terjual)</span></li>
                    @empty
                        <li class="text-gray-500 list-none">Belum ada data penjualan</li>
                    @endforelse
                </ol>
            </x-card>
        </div>
    </div>

    @push('scripts')
    <script type="module">
        import Chart from 'chart.js/auto';

        const ctx = document.getElementById('chartTren');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($labelTren),
                    datasets: [{
                        label: 'Penjualan (Rp)',
                        data: @json($dataTren),
                        borderColor: '#4f46e5',
                        tension: 0.3,
                    }],
                },
            });
        }
    </script>
    <script>
        // Fallback jika browser memuat Chart dari bundled window.Chart
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('chartTren');
            if (ctx && !ctx.$chartInstance && window.Chart) {
                ctx.$chartInstance = new window.Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($labelTren),
                        datasets: [{
                            label: 'Penjualan (Rp)',
                            data: @json($dataTren),
                            borderColor: '#4f46e5',
                            tension: 0.3,
                        }],
                    },
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
