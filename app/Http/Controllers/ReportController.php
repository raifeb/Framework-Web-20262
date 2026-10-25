<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function sales(Request $request): View
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Transaction::with(['user', 'details.product']);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $totalSales = (clone $query)->sum('total');
        $totalTransactions = (clone $query)->count();

        $transactions = $query->latest()->paginate(15)->withQueryString();

        return view('reports.sales', compact('transactions', 'totalSales', 'totalTransactions', 'startDate', 'endDate'));
    }
}

