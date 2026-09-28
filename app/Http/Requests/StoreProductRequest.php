<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Menentukan hak akses pengguna untuk melakukan request ini.
     * Hanya pengguna dengan peran 'admin' yang diperbolehkan menambahkan produk.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Aturan validasi yang berlaku untuk request penambahan produk.
     *
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'code'        => ['required', 'string', 'max:20', 'unique:products,code'],
            'name'        => ['required', 'string', 'max:150'],
            'unit'        => ['required', 'string', 'max:20'],
            'price'       => ['required', 'numeric', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * Kustomisasi nama label atribut untuk pesan validasi.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'kategori',
            'code'        => 'kode produk',
            'name'        => 'nama produk',
            'unit'        => 'satuan',
            'price'       => 'harga',
            'stock'       => 'stok awal',
        ];
    }

    /**
     * Kustomisasi pesan kesalahan validasi dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.required' => ':Attribute wajib dipilih.',
            'category_id.exists'   => ':Attribute yang dipilih tidak valid atau tidak terdaftar.',
            'code.required'        => ':Attribute wajib diisi.',
            'code.unique'          => ':Attribute sudah digunakan, silakan gunakan kode lain.',
            'name.required'        => ':Attribute wajib diisi.',
            'unit.required'        => ':Attribute wajib diisi.',
            'price.required'       => ':Attribute wajib diisi.',
            'price.numeric'        => ':Attribute harus berupa angka numerik.',
            'price.min'            => ':Attribute tidak boleh kurang dari 0.',
            'stock.required'       => ':Attribute wajib diisi.',
            'stock.integer'        => ':Attribute harus berupa bilangan bulat.',
            'stock.min'            => ':Attribute tidak boleh bernilai negatif.',
        ];
    }
}