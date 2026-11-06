<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Pertemuan11And12Test extends TestCase
{
    use DatabaseTransactions;

    /*
    |--------------------------------------------------------------------------
    | PERTEMUAN 11: Detail Produk & Riwayat Penjualan per Produk
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_view_product_detail_with_sales_history_and_total_sold(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Kategori P11', 'description' => 'Desc']);
        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'P11-TEST',
            'name' => 'Minyak Goreng P11',
            'unit' => 'liter',
            'price' => 20000,
            'stock' => 50,
        ]);

        // Buat 3 transaksi penjualan untuk produk ini
        for ($i = 1; $i <= 3; $i++) {
            $trx = Transaction::create([
                'invoice_number' => "INV-P11-000{$i}",
                'user_id' => $admin->id,
                'total' => 40000,
                'pay' => 50000,
                'change' => 10000,
            ]);

            TransactionDetail::create([
                'transaction_id' => $trx->id,
                'product_id' => $product->id,
                'qty' => 2,
                'price' => 20000,
                'subtotal' => 40000,
            ]);
        }

        $response = $this->actingAs($admin)->get(route('products.show', $product));

        $response->assertStatus(200);
        $response->assertSee('Detail Produk');
        $response->assertSee('Minyak Goreng P11');
        $response->assertSee('P11-TEST');
        $response->assertSee('Kategori P11');
        // Total terjual: 3 transaksi x 2 qty = 6
        $response->assertSee('6 liter');
        $response->assertSee('INV-P11-0001');
        $response->assertSee('INV-P11-0002');
        $response->assertSee('INV-P11-0003');
    }

    public function test_pertemuan_11_latihan_1_category_detail_shows_products_and_total_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Kategori Sembako P11', 'description' => 'Kategori bahan pokok']);

        $productA = Product::create([
            'category_id' => $category->id,
            'code' => 'SBK-01',
            'name' => 'Beras Pandan Wangi',
            'unit' => 'kg',
            'price' => 15000,
            'stock' => 30,
        ]);

        $productB = Product::create([
            'category_id' => $category->id,
            'code' => 'SBK-02',
            'name' => 'Gula Pasir Kristal',
            'unit' => 'kg',
            'price' => 17000,
            'stock' => 25,
        ]);

        $response = $this->actingAs($admin)->get(route('categories.show', $category));

        $response->assertStatus(200);
        $response->assertSee('Detail Kategori');
        $response->assertSee('Kategori Sembako P11');
        $response->assertSee('Beras Pandan Wangi');
        $response->assertSee('Gula Pasir Kristal');
        // Total stok: 30 + 25 = 55
        $response->assertSee('55');
    }

    public function test_pertemuan_11_latihan_2_top_5_sales_days_ranking(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Kategori Minuman']);
        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'MNM-01',
            'name' => 'Teh Botol Kotak',
            'unit' => 'kotak',
            'price' => 4000,
            'stock' => 100,
        ]);

        // Buat penjualan di tanggal berbeda
        $dates = [
            '2026-08-01' => 10,
            '2026-08-02' => 25, // Tertinggi 1
            '2026-08-03' => 20, // Tertinggi 2
            '2026-08-04' => 15, // Tertinggi 3
            '2026-08-05' => 12, // Tertinggi 4
            '2026-08-06' => 8,
        ];

        foreach ($dates as $date => $qty) {
            $trx = Transaction::create([
                'invoice_number' => 'INV-' . str_replace('-', '', $date) . '-0001',
                'user_id' => $admin->id,
                'total' => $qty * 4000,
                'pay' => $qty * 4000,
                'change' => 0,
            ]);
            $trx->created_at = Carbon::parse($date);
            $trx->save();

            $detail = TransactionDetail::create([
                'transaction_id' => $trx->id,
                'product_id' => $product->id,
                'qty' => $qty,
                'price' => 4000,
                'subtotal' => $qty * 4000,
            ]);
            $detail->created_at = Carbon::parse($date);
            $detail->save();
        }

        $response = $this->actingAs($admin)->get(route('products.show', $product));

        $response->assertStatus(200);
        $response->assertSee('5 Hari Penjualan Tertinggi');
        $response->assertSee('25 kotak');
        $response->assertSee('20 kotak');
        $response->assertSee('15 kotak');
    }

    /*
    |--------------------------------------------------------------------------
    | PERTEMUAN 12: Transaksi Penjualan (POS)
    |--------------------------------------------------------------------------
    */

    public function test_pos_index_only_lists_products_with_stock_greater_than_zero(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $category = Category::create(['name' => 'Kategori POS']);

        $inStockProduct = Product::create([
            'category_id' => $category->id,
            'code' => 'STK-IN',
            'name' => 'Produk Ada Stok',
            'unit' => 'pcs',
            'price' => 5000,
            'stock' => 10,
        ]);

        $outOfStockProduct = Product::create([
            'category_id' => $category->id,
            'code' => 'STK-OUT',
            'name' => 'Produk Stok Kosong',
            'unit' => 'pcs',
            'price' => 5000,
            'stock' => 0,
        ]);

        $response = $this->actingAs($kasir)->get(route('pos.index'));

        $response->assertStatus(200);
        $response->assertSee('Produk Ada Stok');
        $response->assertDontSee('Produk Stok Kosong');
    }

    public function test_pertemuan_12_latihan_3_insufficient_stock_rolls_back_transaction_completely(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $category = Category::create(['name' => 'Kategori Rollback']);

        // Sesuai skenario modul: Stok diset 1
        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'ROLL-01',
            'name' => 'Produk Kritis',
            'unit' => 'pcs',
            'price' => 12000,
            'stock' => 1,
        ]);

        $initialTransactionCount = Transaction::count();
        $initialDetailCount = TransactionDetail::count();

        // Coba beli 5 unit (melebihi stok yang ada)
        $payload = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 5,
                ],
            ],
            'pay' => 100000,
        ];

        $response = $this->actingAs($kasir)->post(route('pos.store'), $payload);

        // 1. Harus redirect kembali dengan error session
        $response->assertSessionHas('error');

        // 2. Transaksi dibatalkan (rollback): tidak ada transaksi baru
        $this->assertEquals($initialTransactionCount, Transaction::count());
        $this->assertEquals($initialDetailCount, TransactionDetail::count());

        // 3. Stok produk tetap utuh yaitu 1
        $this->assertEquals(1, $product->fresh()->stock);
    }

    public function test_pos_store_successful_atomic_transaction_and_receipt_redirection(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $category = Category::create(['name' => 'Kategori Sukses']);

        $productA = Product::create([
            'category_id' => $category->id,
            'code' => 'SUKSES-01',
            'name' => 'Kopi Kapal Api',
            'unit' => 'bungkus',
            'price' => 3000,
            'stock' => 20,
        ]);

        $productB = Product::create([
            'category_id' => $category->id,
            'code' => 'SUKSES-02',
            'name' => 'Gula Batu',
            'unit' => 'bungkus',
            'price' => 7000,
            'stock' => 15,
        ]);

        $payload = [
            'items' => [
                ['product_id' => $productA->id, 'qty' => 3], // 3 x 3000 = 9000
                ['product_id' => $productB->id, 'qty' => 2], // 2 x 7000 = 14000
            ],
            'pay' => 30000, // Total = 23000, Kembalian = 7000
        ];

        $response = $this->actingAs($kasir)->post(route('pos.store'), $payload);

        $transaction = Transaction::latest('id')->first();
        $response->assertRedirect(route('pos.struk', $transaction));
        $response->assertSessionHas('success');

        // Verifikasi transaksi di database
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'user_id' => $kasir->id,
            'total' => 23000,
            'pay' => 30000,
            'change' => 7000,
        ]);

        // Verifikasi detail transaksi
        $this->assertDatabaseHas('transaction_details', [
            'transaction_id' => $transaction->id,
            'product_id' => $productA->id,
            'qty' => 3,
            'price' => 3000,
            'subtotal' => 9000,
        ]);

        $this->assertDatabaseHas('transaction_details', [
            'transaction_id' => $transaction->id,
            'product_id' => $productB->id,
            'qty' => 2,
            'price' => 7000,
            'subtotal' => 14000,
        ]);

        // Verifikasi pengurangan stok atomik
        $this->assertEquals(17, $productA->fresh()->stock); // 20 - 3
        $this->assertEquals(13, $productB->fresh()->stock); // 15 - 2
    }

    public function test_pos_store_validation_rejects_empty_cart_and_invalid_data(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        // Keranjang kosong
        $response = $this->actingAs($kasir)->post(route('pos.store'), [
            'items' => [],
            'pay' => 10000,
        ]);
        $response->assertSessionHasErrors('items');

        // Qty kurang dari 1
        $response = $this->actingAs($kasir)->post(route('pos.store'), [
            'items' => [
                ['product_id' => 999999, 'qty' => 0],
            ],
            'pay' => 10000,
        ]);
        $response->assertSessionHasErrors(['items.0.product_id', 'items.0.qty']);
    }

    public function test_struk_access_authorization_kasir_own_vs_other_and_admin(): void
    {
        $kasir1 = User::factory()->create(['role' => 'kasir', 'name' => 'Kasir Satu']);
        $kasir2 = User::factory()->create(['role' => 'kasir', 'name' => 'Kasir Dua']);
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Utama']);

        $category = Category::create(['name' => 'Kategori Struk']);
        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'STRUK-01',
            'name' => 'Roti Manis',
            'unit' => 'pcs',
            'price' => 6000,
            'stock' => 10,
        ]);

        $transaction = Transaction::create([
            'invoice_number' => 'INV-STRUK-001',
            'user_id' => $kasir1->id,
            'total' => 6000,
            'pay' => 10000,
            'change' => 4000,
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 6000,
            'subtotal' => 6000,
        ]);

        // 1. Kasir 1 (pemilik) bisa mengakses struknya sendiri
        $response1 = $this->actingAs($kasir1)->get(route('pos.struk', $transaction));
        $response1->assertStatus(200);
        $response1->assertSee('INV-STRUK-001');
        $response1->assertSee('Roti Manis');

        // 2. Kasir 2 dilarang mengakses struk Kasir 1 (403 Forbidden)
        $response2 = $this->actingAs($kasir2)->get(route('pos.struk', $transaction));
        $response2->assertStatus(403);

        // 3. Admin dapat mengakses struk transaksi siapapun (200 OK)
        $responseAdmin = $this->actingAs($admin)->get(route('pos.struk', $transaction));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('INV-STRUK-001');
    }

    public function test_pertemuan_12_latihan_2_pos_history_filters_for_kasir_and_shows_all_for_admin(): void
    {
        $kasir1 = User::factory()->create(['role' => 'kasir', 'name' => 'Kasir Rina']);
        $kasir2 = User::factory()->create(['role' => 'kasir', 'name' => 'Kasir Budi']);
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Toko']);

        $category = Category::create(['name' => 'Kategori History']);
        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'HIST-01',
            'name' => 'Susu Kotak',
            'unit' => 'pcs',
            'price' => 8000,
            'stock' => 30,
        ]);

        $trxKasir1 = Transaction::create([
            'invoice_number' => 'INV-HIST-RINA-01',
            'user_id' => $kasir1->id,
            'total' => 8000,
            'pay' => 10000,
            'change' => 2000,
        ]);
        TransactionDetail::create([
            'transaction_id' => $trxKasir1->id,
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 8000,
            'subtotal' => 8000,
        ]);

        $trxKasir2 = Transaction::create([
            'invoice_number' => 'INV-HIST-BUDI-01',
            'user_id' => $kasir2->id,
            'total' => 16000,
            'pay' => 20000,
            'change' => 4000,
        ]);
        TransactionDetail::create([
            'transaction_id' => $trxKasir2->id,
            'product_id' => $product->id,
            'qty' => 2,
            'price' => 8000,
            'subtotal' => 16000,
        ]);

        // Kasir Rina hanya boleh melihat transaksinya sendiri
        $responseKasir1 = $this->actingAs($kasir1)->get(route('pos.history'));
        $responseKasir1->assertStatus(200);
        $responseKasir1->assertSee('INV-HIST-RINA-01');
        $responseKasir1->assertDontSee('INV-HIST-BUDI-01');

        // Admin melihat kedua transaksi
        $responseAdmin = $this->actingAs($admin)->get(route('pos.history'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('INV-HIST-RINA-01');
        $responseAdmin->assertSee('INV-HIST-BUDI-01');
    }
}
