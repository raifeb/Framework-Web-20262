<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePosRequest;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class POSController extends Controller
{
    /**
     * Menampilkan antarmuka kasir dan daftar produk yang memiliki stok tersedia.
     */
    public function index(): View
    {
        $products = Product::where('stock', '>', 0)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'price', 'stock', 'unit']);

        return view('pos.index', compact('products'));
    }

    /**
     * Memproses dan menyimpan transaksi POS secara atomik menggunakan DB::transaction().
     */
    public function store(StorePosRequest $request): RedirectResponse
    {
        try {
            $transaction = DB::transaction(function () use ($request) {
                $total = 0;
                $details = [];

                // 1. Kunci baris produk dengan lockForUpdate untuk mencegah race condition
                foreach ($request->items as $item) {
                    $product = Product::lockForUpdate()->find($item['product_id']);

                    if (! $product) {
                        throw new \Exception("Produk dengan ID {$item['product_id']} tidak ditemukan.");
                    }

                    if ($product->stock < $item['qty']) {
                        throw new \Exception("Stok untuk produk '{$product->name}' tidak mencukupi.");
                    }

                    // Gunakan harga database, bukan harga inputan dari browser
                    $subtotal = $product->price * $item['qty'];
                    $total += $subtotal;

                    $details[] = [
                        'product_id' => $product->id,
                        'qty'        => $item['qty'],
                        'price'      => $product->price,
                        'subtotal'   => $subtotal,
                    ];

                    // Kurangi stok produk secara atomik
                    $product->decrement('stock', $item['qty']);
                }

                // 2. Validasi kecukupan nominal pembayaran
                if ($request->pay < $total) {
                    throw new \Exception('Nominal bayar kurang dari total belanja.');
                }

                $change = $request->pay - $total;

                // 3. Pembuatan nomor invoice unik dan tahan tabrakan
                $todayCount = Transaction::whereDate('created_at', now()->today())->count() + 1;
                $invoice = 'INV-' . now()->format('Ymd') . '-' . str_pad($todayCount, 4, '0', STR_PAD_LEFT);
                while (Transaction::where('invoice_number', $invoice)->exists()) {
                    $todayCount++;
                    $invoice = 'INV-' . now()->format('Ymd') . '-' . str_pad($todayCount, 4, '0', STR_PAD_LEFT);
                }

                // 4. Simpan header transaksi utama
                $trx = Transaction::create([
                    'invoice_number' => $invoice,
                    'user_id'        => Auth::id() ?? 1,
                    'total'          => $total,
                    'pay'            => $request->pay,
                    'change'         => $change,
                ]);

                // 5. Simpan seluruh rincian transaksi
                $trx->details()->createMany($details);

                return $trx;
            });

            return redirect()->route('pos.struk', $transaction)
                ->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan halaman cetak struk transaksi.
     */
    public function struk(Transaction $transaction): View
    {
        // Otorisasi: Kasir hanya boleh mengakses struk transaksi miliknya sendiri
        if (Auth::user()?->role === 'kasir' && $transaction->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak melihat struk transaksi kasir lain.');
        }

        // Lazy eager loading untuk menghindari query N+1 saat merender detail dan kasir
        $transaction->load(['details.product', 'user']);

        return view('pos.struk', compact('transaction'));
    }

    /**
     * Menampilkan riwayat transaksi (admin melihat semua, kasir melihat miliknya sendiri).
     */
    public function history(): View
    {
        $transactions = Transaction::with(['user', 'details.product'])
            ->when(Auth::user()?->role === 'kasir', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pos.history', compact('transactions'));
    }
}
