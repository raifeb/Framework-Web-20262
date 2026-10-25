<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductStoreRefactorTest extends TestCase
{
    use DatabaseTransactions;

    public function test_store_product(): void
    {
        // 1. Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Umum']);

        $payload = [
            'category_id' => $category->id,
            'code'        => ' brg099 ', // ada spasi
            'name'        => 'Teh Kotak',
            'unit'        => 'PCS',      // huruf besar
            'price'       => 4000,
            'stock'       => 25,
        ];

        // 2. Action
        $response = $this->actingAs($admin)->post(route('products.store'), $payload);

        // 3. Assert
        $response->assertRedirect(route('products.create'));
        $this->assertDatabaseHas('products', [
            'code' => 'BRG099',
            'unit' => 'pcs',
        ]);
    }
}