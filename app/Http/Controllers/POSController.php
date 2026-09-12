<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    /**
     * Menampilkan antarmuka kasir dan daftar produk.
     */
    public function index()
    {
        $products = Product::where('stock', '>', 0)->get();
        return view('pos.index', compact('products'));
    }

    /**
     * Memproses transaksi POS secara atomik.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'pay' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $total = 0;
            $itemsToInsert = [];

            // 1. Hitung total dan verifikasi stok secara pesimistis
            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if ($product->stock < $item['qty']) {
                    throw new \Exception("Stok untuk produk '{$product->name}' tidak mencukupi.");
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
                return back()->with('error', 'Nominal pembayaran kurang dari total belanja.');
            }

            $change = $request->pay - $total;

            // 3. Simpan header transaksi
            $transaction = Transaction::create([
                'invoice_number' => 'INV-' . date('YmdHis') . '-' . rand(100, 999),
                'user_id' => Auth::id() ?? 1, // Fallback ID 1 jika belum implementasi auth login
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

            return redirect()->route('pos.index')->with('success', "Transaksi berhasil! Kembalian: Rp " . number_format($change, 0, ',', '.'));
        });
    }
}