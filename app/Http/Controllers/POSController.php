<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class POSController extends Controller
{
    /**
     * Menampilkan antarmuka kasir dan daftar produk.
     */
    public function index(): View
    {
        $products = Product::with('category')->where('stock', '>', 0)->get();
        return view('pos.index', compact('products'));
    }

    /**
     * Memproses transaksi POS secara atomik.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'pay' => 'required|numeric|min:0',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $total = 0;
                $itemsToInsert = [];

                // 1. Hitung total dan verifikasi stok secara pesimistis
                foreach ($request->items as $item) {
                    $product = Product::lockForUpdate()->find($item['product_id']);

                    if (! $product || $product->stock < $item['qty']) {
                        throw new \Exception("Stok untuk produk '{$product?->name}' tidak mencukupi.");
                    }

                    $subtotal = $product->price * $item['qty'];
                    $total += $subtotal;

                    $itemsToInsert[] = [
                        'product' => $product,
                        'qty' => $item['qty'],
                        'price' => $product->price,
                        'subtotal' => $subtotal,
                    ];
                }

                // 2. Validasi nominal pembayaran
                if ($request->pay < $total) {
                    throw new \Exception('Nominal pembayaran kurang dari total belanja.');
                }

                $change = $request->pay - $total;

                // 3. Simpan header transaksi
                $transaction = Transaction::create([
                    'invoice_number' => 'INV-' . date('YmdHis') . '-' . rand(100, 999),
                    'user_id' => Auth::id() ?? 1,
                    'total' => $total,
                    'pay' => $request->pay,
                    'change' => $change,
                ]);

                // 4. Simpan rincian transaksi & kurangi stok
                foreach ($itemsToInsert as $itemData) {
                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $itemData['product']->id,
                        'qty' => $itemData['qty'],
                        'price' => $itemData['price'],
                        'subtotal' => $itemData['subtotal'],
                    ]);

                    $itemData['product']->decrement('stock', $itemData['qty']);
                }

                return redirect()->route('pos.index')->with('success', "Transaksi {$transaction->invoice_number} berhasil! Kembalian: Rp " . number_format($change, 0, ',', '.'));
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Menampilkan riwayat transaksi.
     */
    public function history(): View
    {
        $transactions = Transaction::with(['user', 'details.product'])
            ->when(auth()->user()->role === 'kasir', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest()
            ->paginate(15);

        return view('pos.history', compact('transactions'));
    }
}
