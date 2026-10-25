<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $productId = $this->product instanceof Product 
            ? $this->product->id 
            : ($this->route('product') instanceof Product ? $this->route('product')->id : $this->route('product'));

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'code'        => ['required', 'string', 'max:20', 'unique:products,code,' . $productId],
            'name'        => ['required', 'string', 'max:150'],
            'unit'        => ['required', 'string', 'max:20'],
            'price'       => ['required', 'numeric', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'kategori',
            'code'        => 'kode produk',
            'name'        => 'nama produk',
            'unit'        => 'satuan',
            'price'       => 'harga',
            'stock'       => 'stok produk',
        ];
    }
}
