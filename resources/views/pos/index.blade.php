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

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Notifikasi --}}
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
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                {{-- Kolom Kiri: Daftar Produk (7 Kolom) --}}
                <div class="lg:col-span-7">
                    <x-card>
                        <div class="flex items-center justify-between border-b border-gray-200 pb-3 mb-4">
                            <h3 class="text-base font-bold text-gray-800">Katalog Produk</h3>
                            <input type="text" id="search-product" placeholder="Cari nama produk..."
                                class="rounded-md border-gray-300 text-sm shadow-sm w-48 sm:w-64 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="overflow-y-auto max-h-[600px] divide-y divide-gray-100">
                            @forelse ($products as $product)
                                <div class="product-item py-3 flex items-center justify-between gap-4 hover:bg-gray-50 px-2 rounded transition"
                                     data-name="{{ strtolower($product->name) }}"
                                     data-code="{{ strtolower($product->code) }}">
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                        <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                                            <span class="bg-gray-100 px-1.5 py-0.5 rounded">{{ $product->code }}</span>
                                            <span>Kategori: {{ $product->category->name ?? '-' }}</span>
                                            <span class="text-gray-600">Stok: <strong>{{ $product->stock }}</strong> {{ $product->unit }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-gray-900 mb-1">
                                            Rp {{ number_format($product->price, 0, ',', '.') }}
                                        </div>
                                        <button type="button"
                                            onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }}, {{ $product->stock }})"
                                            style="background-color: #800020 !important; color: #ffffff !important;"
                                            class="px-3 py-1 rounded text-xs font-medium hover:opacity-90 transition">
                                            + Tambah
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-gray-500 text-sm">
                                    Tidak ada produk tersedia dengan stok aktif.
                                </div>
                            @endforelse
                        </div>
                    </x-card>
                </div>

                {{-- Kolom Kanan: Keranjang Kasir (5 Kolom) --}}
                <div class="lg:col-span-5">
                    <x-card>
                        <div class="flex items-center justify-between border-b border-gray-200 pb-3 mb-4">
                            <h3 class="text-base font-bold text-gray-800">Keranjang Belanja</h3>
                            <button type="button" onclick="clearCart()" class="text-xs text-red-600 hover:underline">
                                Kosongkan
                            </button>
                        </div>

                        <form id="pos-form" action="{{ route('pos.store') }}" method="POST">
                            @csrf

                            {{-- Wadah Item Keranjang --}}
                            <div id="cart-container" class="divide-y divide-gray-100 max-h-[300px] overflow-y-auto mb-4">
                                <div id="empty-cart-msg" class="py-8 text-center text-gray-400 text-sm">
                                    Keranjang masih kosong.<br>Pilih produk di katalog sebelah kiri.
                                </div>
                            </div>

                            {{-- Input Hidden Form Item --}}
                            <div id="cart-inputs"></div>

                            {{-- Rincian Total --}}
                            <div class="border-t border-gray-200 pt-4 space-y-3">
                                <div class="flex justify-between items-center text-sm text-gray-600">
                                    <span>Total Tagihan:</span>
                                    <span id="label-total" class="text-xl font-bold text-gray-900">Rp 0</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                        Nominal Bayar (Rp)
                                    </label>
                                    <input type="number" id="input-pay" name="pay" min="0" step="500" required
                                        placeholder="0"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-base font-semibold focus:border-indigo-500 focus:ring-indigo-500"
                                        oninput="calcChange()">
                                </div>

                                {{-- Tombol Cepat Nominal --}}
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    <button type="button" onclick="setQuickPay('exact')"
                                        class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-xs rounded text-gray-700 transition">
                                        Uang Pas
                                    </button>
                                    <button type="button" onclick="setQuickPay(20000)"
                                        class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-xs rounded text-gray-700 transition">
                                        20.000
                                    </button>
                                    <button type="button" onclick="setQuickPay(50000)"
                                        class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-xs rounded text-gray-700 transition">
                                        50.000
                                    </button>
                                    <button type="button" onclick="setQuickPay(100000)"
                                        class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-xs rounded text-gray-700 transition">
                                        100.000
                                    </button>
                                </div>

                                <div class="flex justify-between items-center text-sm font-medium pt-2">
                                    <span class="text-gray-600">Kembalian:</span>
                                    <span id="label-change" class="text-base font-bold text-green-600">Rp 0</span>
                                </div>

                                <button type="submit" id="btn-submit" disabled
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

    @push('scripts')
    <script>
        let cart = [];

        function formatRp(num) {
            return 'Rp ' + Number(num).toLocaleString('id-ID');
        }

        function addToCart(id, name, price, stock) {
            const existing = cart.find(item => item.id === id);
            if (existing) {
                if (existing.qty < stock) {
                    existing.qty++;
                } else {
                    alert('Jumlah melebihi stok yang tersedia (' + stock + ').');
                    return;
                }
            } else {
                cart.push({ id, name, price, stock, qty: 1 });
            }
            renderCart();
        }

        function updateQty(id, delta) {
            const item = cart.find(i => i.id === id);
            if (!item) return;

            const newQty = item.qty + delta;
            if (newQty <= 0) {
                cart = cart.filter(i => i.id !== id);
            } else if (newQty > item.stock) {
                alert('Jumlah melebihi stok yang tersedia (' + item.stock + ').');
                return;
            } else {
                item.qty = newQty;
            }
            renderCart();
        }

        function removeFromCart(id) {
            cart = cart.filter(i => i.id !== id);
            renderCart();
        }

        function clearCart() {
            cart = [];
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cart-container');
            const inputsDiv = document.getElementById('cart-inputs');
            const emptyMsg = document.getElementById('empty-cart-msg');
            const btnSubmit = document.getElementById('btn-submit');

            container.innerHTML = '';
            inputsDiv.innerHTML = '';

            if (cart.length === 0) {
                container.innerHTML = '<div class="py-8 text-center text-gray-400 text-sm">Keranjang masih kosong.<br>Pilih produk di katalog sebelah kiri.</div>';
                document.getElementById('label-total').innerText = 'Rp 0';
                document.getElementById('input-pay').value = '';
                document.getElementById('label-change').innerText = 'Rp 0';
                btnSubmit.disabled = true;
                return;
            }

            let total = 0;
            cart.forEach((item, index) => {
                const subtotal = item.price * item.qty;
                total += subtotal;

                // Tampilan item keranjang
                const row = document.createElement('div');
                row.className = 'py-2.5 flex items-center justify-between text-sm gap-2';
                row.innerHTML = `
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900 truncate">${item.name}</div>
                        <div class="text-xs text-gray-500">${formatRp(item.price)} &times; ${item.qty}</div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="updateQty(${item.id}, -1)"
                            class="w-6 h-6 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded text-gray-700 text-xs font-bold">-</button>
                        <span class="w-6 text-center text-xs font-semibold">${item.qty}</span>
                        <button type="button" onclick="updateQty(${item.id}, 1)"
                            class="w-6 h-6 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded text-gray-700 text-xs font-bold">+</button>
                    </div>
                    <div class="text-right font-semibold text-gray-900 min-w-[70px]">
                        ${formatRp(subtotal)}
                    </div>
                    <button type="button" onclick="removeFromCart(${item.id})"
                        class="text-red-500 hover:text-red-700 text-xs font-bold px-1">&times;</button>
                `;
                container.appendChild(row);

                // Hidden form fields
                inputsDiv.innerHTML += `
                    <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                    <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
                `;
            });

            document.getElementById('label-total').innerText = formatRp(total);
            calcChange();
        }

        function getTotal() {
            return cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        }

        function calcChange() {
            const total = getTotal();
            const pay = parseFloat(document.getElementById('input-pay').value) || 0;
            const change = pay - total;
            const btnSubmit = document.getElementById('btn-submit');

            if (total > 0 && pay >= total) {
                document.getElementById('label-change').innerText = formatRp(change);
                document.getElementById('label-change').className = 'text-base font-bold text-green-600';
                btnSubmit.disabled = false;
            } else {
                document.getElementById('label-change').innerText = 'Rp 0';
                document.getElementById('label-change').className = 'text-base font-bold text-gray-400';
                btnSubmit.disabled = true;
            }
        }

        function setQuickPay(val) {
            const total = getTotal();
            if (val === 'exact') {
                document.getElementById('input-pay').value = total;
            } else {
                document.getElementById('input-pay').value = val;
            }
            calcChange();
        }

        // Live filter katalog produk
        document.getElementById('search-product')?.addEventListener('input', function (e) {
            const keyword = e.target.value.toLowerCase();
            document.querySelectorAll('.product-item').forEach(item => {
                const name = item.getAttribute('data-name');
                const code = item.getAttribute('data-code');
                if (name.includes(keyword) || code.includes(keyword)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
