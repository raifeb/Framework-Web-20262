<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TransactionAndReportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_access_sales_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('report.sales'));

        $response->assertStatus(200);
        $response->assertSee('Laporan Penjualan');
        $response->assertSee('Total Pendapatan');
    }

    public function test_kasir_cannot_access_sales_report(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        $response = $this->actingAs($kasir)->get(route('report.sales'));

        $response->assertStatus(403);
    }

    public function test_admin_and_kasir_can_access_pos(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kasir = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($admin)->get(route('pos.index'))->assertStatus(200)->assertSee('Transaksi Kasir (POS)');
        $this->actingAs($kasir)->get(route('pos.index'))->assertStatus(200)->assertSee('Transaksi Kasir (POS)');
    }

    public function test_pos_store_creates_transaction_and_decrements_stock(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $category = Category::create(['name' => 'Kategori Tes']);
        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'TEST01',
            'name' => 'Produk Tes',
            'unit' => 'pcs',
            'price' => 10000,
            'stock' => 10,
        ]);

        $payload = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 2,
                ],
            ],
            'pay' => 25000,
        ];

        $response = $this->actingAs($kasir)->post(route('pos.store'), $payload);

        $response->assertRedirect(route('pos.index'));
        $response->assertSessionHas('success');

        // Total 2 * 10.000 = 20.000, Bayar = 25.000, Kembalian = 5.000
        $this->assertDatabaseHas('transactions', [
            'user_id' => $kasir->id,
            'total' => 20000,
            'pay' => 25000,
            'change' => 5000,
        ]);

        $this->assertDatabaseHas('transaction_details', [
            'product_id' => $product->id,
            'qty' => 2,
            'price' => 10000,
            'subtotal' => 20000,
        ]);

        // Stok awal 10 berkurang 2 menjadi 8
        $this->assertEquals(8, $product->fresh()->stock);
    }

    public function test_pos_store_fails_if_payment_is_insufficient(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $category = Category::create(['name' => 'Kategori Tes 2']);
        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'TEST02',
            'name' => 'Produk Kurang Bayar',
            'unit' => 'pcs',
            'price' => 15000,
            'stock' => 5,
        ]);

        $payload = [
            'items' => [
                ['product_id' => $product->id, 'qty' => 1],
            ],
            'pay' => 10000, // Kurang dari 15.000
        ];

        $response = $this->actingAs($kasir)->post(route('pos.store'), $payload);

        $response->assertSessionHas('error');
        $this->assertEquals(5, $product->fresh()->stock);
    }

    public function test_can_access_pos_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kasir = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($admin)->get(route('pos.history'))->assertStatus(200)->assertSee('Riwayat Transaksi');
        $this->actingAs($kasir)->get(route('pos.history'))->assertStatus(200)->assertSee('Riwayat Transaksi');
    }
}

