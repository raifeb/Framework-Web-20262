<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductIndexRefactorTest extends TestCase
{
    use DatabaseTransactions;

    public function test_query(): void
    {
        // 1. Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Umum']);

        for ($i = 1; $i <= 5; $i++) {
            Product::create([
                'category_id' => $category->id,
                'code'        => "PRD0{$i}",
                'name'        => "Produk {$i}",
                'unit'        => 'pcs',
                'price'       => 5000,
                'stock'       => 10,
            ]);
        }

        // 2. Action
        DB::enableQueryLog();
        $response = $this->actingAs($admin)->get(route('products.index'));

        $categoryQueries = collect(DB::getQueryLog())
            ->filter(fn ($q) => str_contains($q['query'], 'categories'))
            ->count();
        DB::disableQueryLog();

        // 3. Assert
        $response->assertStatus(200);
        $this->assertLessThanOrEqual(
            2,
            $categoryQueries,
            "N+1 terdeteksi: {$categoryQueries} query ke categories"
        );
    }
}