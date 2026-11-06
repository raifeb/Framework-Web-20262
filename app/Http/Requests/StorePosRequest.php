<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePosRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan melakukan request ini.
     * Kasir dan Admin berhak memproses transaksi penjualan.
     */
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'kasir']);
    }

    /**
     * Aturan validasi untuk data transaksi keranjang POS.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
            'pay'                => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * Kustomisasi nama atribut untuk pesan validasi ramah pengguna.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'items'              => 'keranjang belanja',
            'items.*.product_id' => 'produk',
            'items.*.qty'        => 'jumlah barang',
            'pay'                => 'nominal pembayaran',
        ];
    }
}

