<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kasir - Barokah Mart</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <div class="flex justify-between items-center border-b pb-4 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Point of Sale (POS)</h1>
                <p class="text-sm text-gray-600">Kasir: {{ auth()->user()->name }} (Role: {{ auth()->user()->role }})</p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
    @csrf
    <button type="submit" 
            style="background-color: #800020 !important; color: #ffffff !important;" 
            class="px-4 py-2 rounded text-sm font-medium hover:opacity-90 transition">
        Logout
    </button>
</form>
        </div>

        <h2 class="text-lg font-semibold mb-3">Daftar Produk Tersedia</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse border border-gray-200">
                <thead>
                    <tr class="bg-gray-50 text-sm font-semibold text-gray-700">
                        <th class="border border-gray-200 px-4 py-2">Nama Produk</th>
                        <th class="border border-gray-200 px-4 py-2">Harga</th>
                        <th class="border border-gray-200 px-4 py-2">Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="text-sm">
                            <td class="border border-gray-200 px-4 py-2">{{ $product->name }}</td>
                            <td class="border border-gray-200 px-4 py-2">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                            <td class="border border-gray-200 px-4 py-2">{{ $product->stock }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-gray-500">Tidak ada stok produk yang tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>