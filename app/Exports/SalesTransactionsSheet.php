<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesTransactionsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(
        protected $start,
        protected $end,
        protected $userId = null
    ) {}

    public function collection(): Collection
    {
        $query = Transaction::with('user')
            ->whereBetween('created_at', [$this->start, $this->end]);

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return ['No. Invoice', 'Tanggal', 'Kasir', 'Total', 'Bayar', 'Kembalian'];
    }

    public function map($transaction): array
    {
        return [
            $transaction->invoice_number,
            $transaction->created_at->format('d-m-Y H:i'),
            $transaction->user->name ?? 'Kasir #' . $transaction->user_id,
            $transaction->total,
            $transaction->pay,
            $transaction->change,
        ];
    }

    public function title(): string
    {
        return 'Laporan Penjualan';
    }
}

