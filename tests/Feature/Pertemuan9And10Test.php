<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Pertemuan9And10Test extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $kasir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->kasir = User::factory()->create(['role' => 'kasir']);
    }

    /*
    |--------------------------------------------------------------------------
    | Pertemuan 9: Edit, Update, Destroy Produk & Kategori, Soft Delete
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_view_product_edit_form_with_initial_values(): void
    {
        $category = Category::create(['name' => 'Snack']);
        $product = Product::create([
            'category_id' => $category->id,
            'code'        => 'SNK01',
            'name'        => 'Keripik Singkong',
            'unit'        => 'pcs',
            'price'       => 8000,
            'stock'       => 20,
        ]);

        $response = $this->actingAs($this->admin)->get(route('products.edit', $product));

        $response->assertStatus(200);
        $response->assertSee('Formulir Edit Produk');
        $response->assertSee('SNK01');
        $response->assertSee('Keripik Singkong');
    }

    public function test_admin_can_update_product_retaining_same_code(): void
    {
        $category = Category::create(['name' => 'Minuman']);
        $product = Product::create([
            'category_id' => $category->id,
            'code'        => 'MNM01',
            'name'        => 'Teh Pucuk 350ml',
            'unit'        => 'botol',
            'price'       => 4000,
            'stock'       => 30,
        ]);

        $response = $this->actingAs($this->admin)->put(route('products.update', $product), [
            'category_id' => $category->id,
            'code'        => 'MNM01', // Kode tetap sama
            'name'        => 'Teh Pucuk Harum 350ml', // Nama diubah
            'unit'        => 'botol',
            'price'       => 4500, // Harga naik
            'stock'       => 25,
        ]);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id'    => $product->id,
            'name'  => 'Teh Pucuk Harum 350ml',
            'price' => 4500,
            'stock' => 25,
        ]);
    }

    public function test_admin_cannot_update_product_with_duplicate_code_from_other_product(): void
    {
        $category = Category::create(['name' => 'Biskuit']);
        $product1 = Product::create([
            'category_id' => $category->id,
            'code'        => 'BSK01',
            'name'        => 'Oreo Vanilla',
            'unit'        => 'pcs',
            'price'       => 8500,
            'stock'       => 15,
        ]);

        $product2 = Product::create([
            'category_id' => $category->id,
            'code'        => 'BSK02',
            'name'        => 'Ritz Cheese',
            'unit'        => 'pcs',
            'price'       => 9000,
            'stock'       => 10,
        ]);

        // Coba ganti kode product2 menjadi kode milik product1
        $response = $this->actingAs($this->admin)->put(route('products.update', $product2), [
            'category_id' => $category->id,
            'code'        => 'BSK01',
            'name'        => 'Ritz Cheese Updated',
            'unit'        => 'pcs',
            'price'       => 9000,
            'stock'       => 10,
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_admin_can_soft_delete_product(): void
    {
        $category = Category::create(['name' => 'Sembako']);
        $product = Product::create([
            'category_id' => $category->id,
            'code'        => 'SBK01',
            'name'        => 'Beras Rojolele 5kg',
            'unit'        => 'karung',
            'price'       => 72000,
            'stock'       => 8,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');

        // Pastikan soft delete: baris ada di DB tetapi deleted_at tidak null
        $this->assertSoftDeleted('products', ['id' => $product->id]);

        // Tidak muncul pada query biasa
        $this->assertNull(Product::find($product->id));
        // Muncul pada withTrashed()
        $this->assertNotNull(Product::withTrashed()->find($product->id));
    }

    public function test_admin_can_update_category(): void
    {
        $category = Category::create([
            'name'        => 'Makanan Lama',
            'description' => 'Deskripsi lama',
        ]);

        $response = $this->actingAs($this->admin)->put(route('categories.update', $category), [
            'name'        => 'Makanan Olahan',
            'description' => 'Deskripsi diperbarui',
        ]);

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'id'          => $category->id,
            'name'        => 'Makanan Olahan',
            'description' => 'Deskripsi diperbarui',
        ]);
    }

    public function test_category_cannot_be_deleted_if_it_still_has_products(): void
    {
        $category = Category::create(['name' => 'Kategori Terisi']);
        Product::create([
            'category_id' => $category->id,
            'code'        => 'ISI01',
            'name'        => 'Produk Contoh',
            'unit'        => 'pcs',
            'price'       => 5000,
            'stock'       => 10,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('error');

        // Kategori tidak terhapus
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_empty_category_can_be_deleted(): void
    {
        $category = Category::create(['name' => 'Kategori Kosong']);

        $response = $this->actingAs($this->admin)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Pertemuan 10: Search, Category Filter, Stock Status Filter, Pagination
    |--------------------------------------------------------------------------
    */

    public function test_product_search_by_name_and_code(): void
    {
        $category = Category::create(['name' => 'Toiletries']);
        $p1 = Product::create([
            'category_id' => $category->id,
            'code'        => 'SBN88',
            'name'        => 'Sabun Mandi Dettol',
            'unit'        => 'batang',
            'price'       => 6000,
            'stock'       => 20,
        ]);
        $p2 = Product::create([
            'category_id' => $category->id,
            'code'        => 'SKT99',
            'name'        => 'Sikat Gigi Formula',
            'unit'        => 'pcs',
            'price'       => 4500,
            'stock'       => 15,
        ]);

        // Cari berdasarkan nama
        $resName = $this->actingAs($this->admin)->get(route('products.index', ['search' => 'Dettol']));
        $resName->assertSee('Sabun Mandi Dettol');
        $resName->assertDontSee('Sikat Gigi Formula');

        // Cari berdasarkan kode
        $resCode = $this->actingAs($this->admin)->get(route('products.index', ['search' => 'SKT99']));
        $resCode->assertSee('Sikat Gigi Formula');
        $resCode->assertDontSee('Sabun Mandi Dettol');
    }

    public function test_product_filter_by_category(): void
    {
        $catA = Category::create(['name' => 'Kategori Alpha']);
        $catB = Category::create(['name' => 'Kategori Beta']);

        $pA = Product::create([
            'category_id' => $catA->id,
            'code'        => 'ALP01',
            'name'        => 'Produk Alpha Eksklusif',
            'unit'        => 'pcs',
            'price'       => 10000,
            'stock'       => 12,
        ]);

        $pB = Product::create([
            'category_id' => $catB->id,
            'code'        => 'BET01',
            'name'        => 'Produk Beta Eksklusif',
            'unit'        => 'pcs',
            'price'       => 15000,
            'stock'       => 12,
        ]);

        $response = $this->actingAs($this->admin)->get(route('products.index', ['category_id' => $catA->id]));

        $response->assertSee('Produk Alpha Eksklusif');
        $response->assertDontSee('Produk Beta Eksklusif');
    }

    public function test_product_filter_by_stock_status(): void
    {
        $category = Category::create(['name' => 'Stok Tes']);
        $pHabis = Product::create([
            'category_id' => $category->id,
            'code'        => 'STK-HBS',
            'name'        => 'Barang Stok Habis',
            'unit'        => 'pcs',
            'price'       => 1000,
            'stock'       => 0,
        ]);
        $pMenipis = Product::create([
            'category_id' => $category->id,
            'code'        => 'STK-NPS',
            'name'        => 'Barang Stok Menipis',
            'unit'        => 'pcs',
            'price'       => 2000,
            'stock'       => 5,
        ]);
        $pAman = Product::create([
            'category_id' => $category->id,
            'code'        => 'STK-AMN',
            'name'        => 'Barang Stok Aman',
            'unit'        => 'pcs',
            'price'       => 3000,
            'stock'       => 50,
        ]);

        // Filter habis
        $resHabis = $this->actingAs($this->admin)->get(route('products.index', ['stock_status' => 'habis']));
        $resHabis->assertSee('Barang Stok Habis');
        $resHabis->assertDontSee('Barang Stok Aman');

        // Filter menipis
        $resMenipis = $this->actingAs($this->admin)->get(route('products.index', ['stock_status' => 'menipis']));
        $resMenipis->assertSee('Barang Stok Menipis');
        $resMenipis->assertDontSee('Barang Stok Habis');

        // Filter aman
        $resAman = $this->actingAs($this->admin)->get(route('products.index', ['stock_status' => 'aman']));
        $resAman->assertSee('Barang Stok Aman');
        $resAman->assertDontSee('Barang Stok Habis');
    }

    public function test_pagination_and_query_string_persistence(): void
    {
        $category = Category::create(['name' => 'Paging Cat']);

        // Buat 15 produk dengan kata kunci unik
        for ($i = 1; $i <= 15; $i++) {
            Product::create([
                'category_id' => $category->id,
                'code'        => sprintf('PAGE%02d', $i),
                'name'        => "Kecap Manis Cap {$i}",
                'unit'        => 'botol',
                'price'       => 10000,
                'stock'       => 20,
            ]);
        }

        // Buka halaman dengan search dan per_page=10
        $response = $this->actingAs($this->admin)->get(route('products.index', [
            'search'      => 'Kecap',
            'category_id' => $category->id,
            'per_page'    => 10,
        ]));

        $response->assertStatus(200);

        // Tautan pagination mempertahankan parameter
        $response->assertSee('search=Kecap');
        $response->assertSee('category_id=' . $category->id);
    }
}

