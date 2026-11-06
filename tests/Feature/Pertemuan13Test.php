<?php

namespace Tests\Feature;

use App\Exports\SalesExport;
use App\Exports\SalesTransactionsSheet;
use App\Exports\TopSellingProductsSheet;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class Pertemuan13Test extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $kasir1;
    protected User $kasir2;
    protected Product $productA;
    protected Product $productB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Utama']);
        $this->kasir1 = User::factory()->create(['role' => 'kasir', 'name' => 'Budi Kasir']);
        $this->kasir2 = User::factory()->create(['role' => 'kasir', 'name' => 'Siti Kasir']);

        $category = Category::create(['name' => 'Sembako']);
        $this->productA = Product::create([
            'category_id' => $category->id,
            'code' => 'BRG-P13-A',
            'name' => 'Beras Pandan Wangi',
            'unit' => 'kg',
            'price' => 15000,
            'stock' => 50,
        ]);

        $this->productB = Product::create([
            'category_id' => $category->id,
            'code' => 'BRG-P13-B',
            'name' => 'Minyak Goreng 1L',
            'unit' => 'liter',
            'price' => 18000,
            'stock' => 30,
        ]);
    }

    public function test_admin_can_access_report_sales_with_default_range(): void
    {
        $response = $this->actingAs($this->admin)->get(route('report.sales'));

        $response->assertStatus(200);
        $response->assertSee('Laporan Penjualan');
        $response->assertSee('Dari Tanggal');
        $response->assertSee('Sampai Tanggal');
        $response->assertSee('Export Excel');
        $response->assertSee('Total Penjualan');
        $response->assertSee('Jumlah Transaksi');
    }

    public function test_kasir_cannot_access_report_sales(): void
    {
        $response = $this->actingAs($this->kasir1)->get(route('report.sales'));

        $response->assertStatus(403);
    }

    public function test_admin_can_filter_sales_by_date_range(): void
    {
        // Transaksi 1: 3 hari lalu
        $trxOld = Transaction::create([
            'invoice_number' => 'INV-OLD-01',
            'user_id' => $this->kasir1->id,
            'total' => 30000,
            'pay' => 50000,
            'change' => 20000,
        ]);
        $trxOld->created_at = now()->subDays(3);
        $trxOld->saveQuietly();

        // Transaksi 2: 15 hari lalu (di luar filter)
        $trxOut = Transaction::create([
            'invoice_number' => 'INV-OUT-01',
            'user_id' => $this->kasir1->id,
            'total' => 100000,
            'pay' => 100000,
            'change' => 0,
        ]);
        $trxOut->created_at = now()->subDays(15);
        $trxOut->saveQuietly();

        $start = now()->subDays(5)->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $response = $this->actingAs($this->admin)->get(route('report.sales', [
            'start' => $start,
            'end' => $end,
        ]));

        $response->assertStatus(200);
        $response->assertSee('INV-OLD-01');
        $response->assertDontSee('INV-OUT-01');
    }

    public function test_admin_can_filter_sales_by_cashier(): void
    {
        // Latihan 2: Filter berdasarkan kasir tertentu
        $trxKasir1 = Transaction::create([
            'invoice_number' => 'INV-KASIR1',
            'user_id' => $this->kasir1->id,
            'total' => 25000,
            'pay' => 30000,
            'change' => 5000,
            'created_at' => now()->subDay(),
        ]);

        $trxKasir2 = Transaction::create([
            'invoice_number' => 'INV-KASIR2',
            'user_id' => $this->kasir2->id,
            'total' => 45000,
            'pay' => 50000,
            'change' => 5000,
            'created_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('report.sales', [
            'user_id' => $this->kasir1->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('INV-KASIR1');
        $response->assertDontSee('INV-KASIR2');
    }

    public function test_admin_can_download_sales_excel_export(): void
    {
        Excel::fake();

        $start = now()->subDays(7)->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $response = $this->actingAs($this->admin)->get(route('report.export', [
            'start' => $start,
            'end' => $end,
        ]));

        $response->assertStatus(200);

        $expectedFilename = 'laporan-penjualan-' . Carbon::parse($start)->format('Ymd') . '-' . Carbon::parse($end)->format('Ymd') . '.xlsx';

        Excel::assertDownloaded($expectedFilename, function (SalesExport $export) {
            return true;
        });
    }

    public function test_kasir_cannot_download_sales_excel_export(): void
    {
        $response = $this->actingAs($this->kasir1)->get(route('report.export'));

        $response->assertStatus(403);
    }

    public function test_sales_export_class_contracts_and_multiple_sheets(): void
    {
        // Memastikan SalesExport memenuhi contract modul 13.3 dan Latihan 1
        $start = now()->subDays(5);
        $end = now();

        $trx = Transaction::create([
            'invoice_number' => 'INV-MULTI-01',
            'user_id' => $this->kasir1->id,
            'total' => 36000,
            'pay' => 40000,
            'change' => 4000,
            'created_at' => now()->subDay(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $trx->id,
            'product_id' => $this->productB->id,
            'qty' => 2,
            'price' => 18000,
            'subtotal' => 36000,
        ]);

        $export = new SalesExport($start, $end);

        // Kontrak Dasar (13.3)
        $this->assertInstanceOf(FromCollection::class, $export);
        $this->assertInstanceOf(WithHeadings::class, $export);
        $this->assertInstanceOf(WithMapping::class, $export);
        $this->assertInstanceOf(WithMultipleSheets::class, $export);

        // Headings & Collection
        $this->assertEquals(['No. Invoice', 'Tanggal', 'Kasir', 'Total', 'Bayar', 'Kembalian'], $export->headings());
        $collection = $export->collection();
        $this->assertTrue($collection->contains('id', $trx->id));

        $mapped = $export->map($trx);
        $this->assertEquals('INV-MULTI-01', $mapped[0]);
        $this->assertEquals('Budi Kasir', $mapped[2]);
        $this->assertEquals(36000, $mapped[3]);

        // Latihan 1: Multi-Sheet
        $sheets = $export->sheets();
        $this->assertCount(2, $sheets);
        $this->assertInstanceOf(SalesTransactionsSheet::class, $sheets[0]);
        $this->assertInstanceOf(TopSellingProductsSheet::class, $sheets[1]);

        $this->assertEquals('Laporan Penjualan', $sheets[0]->title());
        $this->assertEquals('Produk Terlaris', $sheets[1]->title());

        // Verifikasi data sheet produk terlaris
        $topProducts = $sheets[1]->collection();
        $this->assertTrue($topProducts->contains('code', 'BRG-P13-B'));
    }
}
