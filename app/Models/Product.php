<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Kolom yang dapat diisi secara massal (Mass Assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'code',
        'name',
        'image',
        'unit',
        'price',
        'stock',
    ];

    /**
     * Relasi: Setiap Produk dimiliki oleh satu Kategori (Many to One).
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi: Produk memiliki banyak rincian transaksi (One to Many).
     *
     * @return HasMany
     */
    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    /**
     * Accessor: Format nominal harga ke format mata uang Rupiah (IDR).
     * Dapat diakses dengan atribut: $product->formatted_price
     *
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Accessor: Status ketersediaan stok produk (aman, menipis, habis).
     * Dapat diakses dengan atribut: $product->stock_status
     *
     * @return string
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0) {
            return 'habis';
        }

        if ($this->stock <= 10) {
            return 'menipis';
        }

        return 'aman';
    }
}


