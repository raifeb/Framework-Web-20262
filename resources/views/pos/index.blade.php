<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Transaksi Kasir (POS)') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Kasir: <span class="font-medium text-gray-800">{{ auth()->user()->name }}</span> ({{ ucfirst(auth()->user()->role) }})
                </p>
            </div>
            <a href="{{ route('pos.history') }}"
                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm font-medium transition">
                &larr; Riwayat Transaksi
            </a>
        </div>
    </x-slot>

    <script>
        function posCart(products) {
            return {
                products: products || [],
                cart: [],
                keyword: '',
                pay: 0,

                filteredProducts() {
                    if (!this.keyword || this.keyword.trim() === '') {
                        return this.products;
                    }
                    const k = this.keyword.toLowerCase().trim();
                    return this.products.filter(p =>
                        (p.name && p.name.toLowerCase().includes(k)) ||
                        (p.code && p.code.toLowerCase().includes(k))
                    );
                },

                addItem(product) {
                    const existing = this.cart.find(i => i.id === product.id);
                    if (existing) {
                        if (existing.qty < product.stock) {
                            existing.qty++;
                        } else {
                            alert('Jumlah melebihi stok yang tersedia (' + product.stock + ').');
                        }
                        return;
                    }
                    this.cart.push({
                        id: product.id,
                        name: product.name,
                        price: Number(product.price),
                        stock: Number(product.stock),
                        unit: product.unit || 'pcs',
                        qty: 1
                    });
                },

                updateQty(item, delta) {
                    const newQty = item.qty + delta;
                    if (newQty <= 0) {
                        this.removeItem(this.cart.indexOf(item));
                    } else if (newQty > item.stock) {
                        alert('Jumlah melebihi stok yang tersedia (' + item.stock + ').');
                    } else {
                        item.qty = newQty;
                    }
                },

                clampQty(item) {
                    if (!item.qty || item.qty < 1) item.qty = 1;
                    if (item.qty > item.stock) {
                        alert('Jumlah disesuaikan dengan sisa stok (' + item.stock + ').');
                        item.qty = item.stock;
                    }
                },

                removeItem(index) {
                    this.cart.splice(index, 1);
                },

                clearCart() {
                    this.cart = [];
                    this.pay = 0;
                },

                total() {
                    return this.cart.reduce((sum, i) => sum + (Number(i.price) * Number(i.qty)), 0);
                },

                change() {
                    return Math.max(Number(this.pay || 0) - this.total(), 0);
                },

                setQuickPay(val) {
                    if (val === 'exact') {
                        this.pay = this.total();
                    } else {
                        this.pay = Number(val);
                    }
                },

                formatRupiah(value) {
                    return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
                }
            };
        }
    </script>

    <div class="py-8" x-data="posCart({{ $products->toJson() }})">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="p-4 bg-green-100 border border-green-200 text-green-800 rounded-lg text-sm font-medium flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-900">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-red-100 border border-red-200 text-red-800 rounded-lg text-sm font-medium flex items-center justify-between">
                    <span>{{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-900">&times;</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-100 border border-red-200 text-red-800 rounded-lg text-sm">
                    <p class="font-semibold mb-1">Gagal memproses transaksi:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                {{-- Kolom Kiri: Katalog Produk (7 Kolom) --}}
                <div class="lg:col-span-7">
                    <x-card>
                        <div class="flex items-center justify-between border-b border-gray-200 pb-3 mb-4">
                            <h3 class="text-base font-bold text-gray-800">Katalog Produk</h3>
                            <input type="text" x-model="keyword" placeholder="Cari nama atau kode produk..."
                                class="rounded-md border-gray-300 text-sm shadow-sm w-48 sm:w-64 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="overflow-y-auto max-h-[560px] divide-y divide-gray-100">
                            <template x-for="product in filteredProducts()" :key="product.id">
                                <div class="py-3 flex items-center justify-between gap-4 hover:bg-gray-50 px-2 rounded transition">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-gray-900 truncate" x-text="product.name"></div>
                                        <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                                            <span class="bg-gray-100 px-1.5 py-0.5 rounded font-mono" x-text="product.code"></span>
                                            <span>Stok: <strong x-text="product.stock"></strong> <span x-text="product.unit || 'pcs'"></span></span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-gray-900 mb-1" x-text="formatRupiah(product.price)"></div>
                                        <button type="button" @click="addItem(product)"
                                            style="background-color: #800020 !important; color: #ffffff !important;"
                                            class="px-3 py-1 rounded text-xs font-medium hover:opacity-90 transition">
                                            + Tambah
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <div x-show="filteredProducts().length === 0" class="py-12 text-center text-gray-400 text-sm">
                                Tidak ada produk yang sesuai dengan pencarian.
                            </div>
                        </div>
                    </x-card>
                </div>

                {{-- Kolom Kanan: Keranjang Transaksi (5 Kolom) --}}
                <div class="lg:col-span-5">
                    <x-card>
                        <div class="flex items-center justify-between border-b border-gray-200 pb-3 mb-4">
                            <h3 class="text-base font-bold text-gray-800">Keranjang Belanja</h3>
                            <button type="button" @click="clearCart()" x-show="cart.length > 0"
                                class="text-xs text-red-600 hover:underline">
                                Kosongkan
                            </button>
                        </div>

                        <form action="{{ route('pos.store') }}" method="POST">
                            @csrf

                            {{-- Daftar Item Keranjang --}}
                            <div class="divide-y divide-gray-100 max-h-[300px] overflow-y-auto mb-4">
                                <template x-for="(item, index) in cart" :key="item.id">
                                    <div class="py-2.5 flex items-center justify-between text-sm gap-2">
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-gray-900 truncate" x-text="item.name"></div>
                                            <div class="text-xs text-gray-500" x-text="formatRupiah(item.price) + ' x ' + item.qty"></div>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" @click="updateQty(item, -1)"
                                                class="w-6 h-6 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded text-gray-700 text-xs font-bold">-</button>
                                            <input type="number" min="1" :max="item.stock" x-model.number="item.qty"
                                                @input="clampQty(item)"
                                                class="w-14 text-center rounded border-gray-300 text-xs py-1 px-1">
                                            <button type="button" @click="updateQty(item, 1)"
                                                class="w-6 h-6 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded text-gray-700 text-xs font-bold">+</button>
                                        </div>
                                        <div class="text-right font-semibold text-gray-900 min-w-[70px]"
                                            x-text="formatRupiah(item.price * item.qty)">
                                        </div>
                                        <button type="button" @click="removeItem(index)"
                                            class="text-red-500 hover:text-red-700 text-base font-bold px-1">&times;</button>

                                        {{-- Hidden inputs for form submission --}}
                                        <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.id">
                                        <input type="hidden" :name="'items[' + index + '][qty]'" :value="item.qty">
                                    </div>
                                </template>

                                <div x-show="cart.length === 0" class="py-8 text-center text-gray-400 text-sm">
                                    Keranjang masih kosong.<br>Pilih produk di katalog sebelah kiri.
                                </div>
                            </div>

                            {{-- Ringkasan dan Pembayaran --}}
                            <div class="border-t border-gray-200 pt-4 space-y-3">
                                <div class="flex justify-between items-center text-sm text-gray-600">
                                    <span>Total Tagihan:</span>
                                    <span class="text-xl font-bold text-gray-900" x-text="formatRupiah(total())"></span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                        Nominal Bayar (Rp)
                                    </label>
                                    <input type="number" name="pay" x-model.number="pay" min="0" step="500" required
                                        placeholder="0"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-base font-semibold focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                {{-- Quick Pay Buttons --}}
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    <button type="button" @click="setQuickPay('exact')"
                                        class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-xs rounded text-gray-700 transition">
                                        Uang Pas
                                    </button>
                                    <button type="button" @click="setQuickPay(20000)"
                                        class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-xs rounded text-gray-700 transition">
                                        20.000
                                    </button>
                                    <button type="button" @click="setQuickPay(50000)"
                                        class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-xs rounded text-gray-700 transition">
                                        50.000
                                    </button>
                                    <button type="button" @click="setQuickPay(100000)"
                                        class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-xs rounded text-gray-700 transition">
                                        100.000
                                    </button>
                                </div>

                                <div class="flex justify-between items-center text-sm font-medium pt-2">
                                    <span class="text-gray-600">Kembalian:</span>
                                    <span class="text-base font-bold"
                                        :class="total() > 0 && pay >= total() ? 'text-green-600' : 'text-gray-400'"
                                        x-text="formatRupiah(change())"></span>
                                </div>

                                <button type="submit" :disabled="cart.length === 0 || pay < total()"
                                    style="background-color: #800020 !important; color: #ffffff !important;"
                                    class="w-full py-2.5 rounded-md font-semibold text-sm hover:opacity-90 disabled:opacity-50 transition cursor-pointer">
                                    Selesaikan Transaksi
                                </button>
                            </div>
                        </form>
                    </x-card>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
