<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Pertemuan14Test extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $kasir;
    protected Product $productA;
    protected Product $productB;
    protected Product $productLowStock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->kasir = User::factory()->create(['role' => 'kasir']);

        $category = Category::create(['name' => 'Kategori P14']);

        $this->productA = Product::create([
            'category_id' => $category->id,
            'code' => 'BRG-P14-A',
            'name' => 'Kecap Manis 500ml',
            'unit' => 'botol',
            'price' => 12000,
            'stock' => 25,
        ]);

        $this->productB = Product::create([
            'category_id' => $category->id,
            'code' => 'BRG-P14-B',
            'name' => 'Saus Sambal 335ml',
            'unit' => 'botol',
            'price' => 15000,
            'stock' => 30,
        ]);

        $this->productLowStock = Product::create([
            'category_id' => $category->id,
            'code' => 'BRG-P14-LOW',
            'name' => 'Garam Dapur 250g',
            'unit' => 'bks',
            'price' => 3000,
            'stock' => 5, // < 10 (stok menipis)
        ]);
    }

    public function test_authenticated_admin_and_kasir_can_access_dashboard_with_analytics_ui(): void
    {
        foreach ([$this->admin, $this->kasir] as $user) {
            $response = $this->actingAs($user)->get(route('dashboard'));

            $response->assertStatus(200);
            $response->assertViewIs('dashboard');
            $response->assertViewHasAll([
                'omzetHariIni',
                'omzetKemarin',
                'transaksiHariIni',
                'produkMenipis',
                'labelTren',
                'dataTren',
                'produkTerlaris',
            ]);

            $response->assertSee('Omzet Hari Ini');
            $response->assertSee('Omzet Kemarin');
            $response->assertSee('Transaksi Hari Ini');
            $response->assertSee('Produk Stok Menipis');
            $response->assertSee('Tren Penjualan 7 Hari Terakhir');
            $response->assertSee('Produk Terlaris');
            $response->assertSee('chartTren');
        }
    }

    public function test_dashboard_analytics_calculations_accurate(): void
    {
        // 1. Transaksi Hari Ini
        $trxToday1 = Transaction::create([
            'invoice_number' => 'INV-TODAY-01',
            'user_id' => $this->kasir->id,
            'total' => 24000,
            'pay' => 30000,
            'change' => 6000,
        ]);

        $trxToday2 = Transaction::create([
            'invoice_number' => 'INV-TODAY-02',
            'user_id' => $this->kasir->id,
            'total' => 30000,
            'pay' => 50000,
            'change' => 20000,
        ]);

        // Details untuk produk terlaris
        TransactionDetail::create([
            'transaction_id' => $trxToday1->id,
            'product_id' => $this->productA->id,
            'qty' => 2,
            'price' => 12000,
            'subtotal' => 24000,
        ]);

        TransactionDetail::create([
            'transaction_id' => $trxToday2->id,
            'product_id' => $this->productB->id,
            'qty' => 2,
            'price' => 15000,
            'subtotal' => 30000,
        ]);

        // 2. Transaksi Kemarin
        $trxYesterday = Transaction::create([
            'invoice_number' => 'INV-YEST-01',
            'user_id' => $this->kasir->id,
            'total' => 50000,
            'pay' => 50000,
            'change' => 0,
        ]);
        $trxYesterday->created_at = today()->subDay()->setTime(14, 0);
        $trxYesterday->saveQuietly();

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);

        // Omzet Hari Ini = minimal 24.000 + 30.000 = 54.000
        $this->assertGreaterThanOrEqual(54000, $response->viewData('omzetHariIni'));
        // Transaksi Hari Ini >= 2
        $this->assertGreaterThanOrEqual(2, $response->viewData('transaksiHariIni'));
        // Omzet Kemarin >= 50.000
        $this->assertGreaterThanOrEqual(50000, $response->viewData('omzetKemarin'));
        // Produk Stok Menipis (stok < 10) >= 1
        $this->assertGreaterThanOrEqual(1, $response->viewData('produkMenipis'));

        // Label tren dan data tren tepat 7 data point (7 hari)
        $labels = $response->viewData('labelTren');
        $data = $response->viewData('dataTren');
        $this->assertCount(7, $labels);
        $this->assertCount(7, $data);
        $this->assertEquals(today()->format('Y-m-d'), $labels->last());

        // Produk terlaris tampil di dashboard
        $response->assertSee($this->productA->name);
        $response->assertSee($this->productB->name);
    }

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }
}

