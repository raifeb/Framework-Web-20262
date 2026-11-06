<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function sales(Request $request): View
    {
        $start = $request->filled('start') ? Carbon::parse($request->start) : now()->subDays(6)->startOfDay();
        $end = $request->filled('end') ? Carbon::parse($request->end)->endOfDay() : now()->endOfDay();

        $query = Transaction::with(['user', 'details.product'])
            ->whereBetween('created_at', [$start, $end]);

        // Latihan 2: Filter berdasarkan kasir tertentu
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $transactions = $query->latest()->get();

        $totalPenjualan = $transactions->sum('total');
        $jumlahTransaksi = $transactions->count();

        // Daftar kasir & admin untuk dropdown filter
        $cashiers = User::whereIn('role', ['kasir', 'admin'])->orderBy('name')->get();

        return view('reports.sales', compact('transactions', 'totalPenjualan', 'jumlahTransaksi', 'start', 'end', 'cashiers'));
    }

    public function export(Request $request): BinaryFileResponse
    {
        $start = $request->filled('start') ? Carbon::parse($request->start) : now()->subDays(6)->startOfDay();
        $end = $request->filled('end') ? Carbon::parse($request->end)->endOfDay() : now()->endOfDay();

        $userId = $request->input('user_id');

        $filename = 'laporan-penjualan-' . $start->format('Ymd') . '-' . $end->format('Ymd') . '.xlsx';

        return Excel::download(new SalesExport($start, $end, $userId), $filename);
    }
}
