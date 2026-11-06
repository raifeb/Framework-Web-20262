<?php

namespace App\Exports;

use App\Models\TransactionDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class TopSellingProductsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(
        protected $start,
        protected $end,
        protected $userId = null
    ) {}

    public function collection(): Collection
    {
        return TransactionDetail::join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('transactions.created_at', [$this->start, $this->end])
            ->when($this->userId, fn($q) => $q->where('transactions.user_id', $this->userId))
            ->select(
                'products.code',
                'products.name',
                'categories.name as category_name',
                DB::raw('SUM(transaction_details.qty) as total_qty'),
                DB::raw('SUM(transaction_details.subtotal) as total_sales')
            )
            ->groupBy('products.id', 'products.code', 'products.name', 'categories.name')
            ->orderByDesc('total_qty')
            ->get();
    }

    public function headings(): array
    {
        return ['Kode Produk', 'Nama Produk', 'Kategori', 'Total Terjual (Qty)', 'Total Pendapatan (Rp)'];
    }

    public function map($row): array
    {
        return [
            $row->code,
            $row->name,
            $row->category_name ?? '-',
            (int) $row->total_qty,
            (float) $row->total_sales,
        ];
    }

    public function title(): string
    {
        return 'Produk Terlaris';
    }
}

