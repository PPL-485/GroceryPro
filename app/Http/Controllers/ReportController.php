<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query();

        if (auth()->user()->role === 'cashier') {
            $query->where('user_id', auth()->id());
        }

        $filter = $request->input('filter', 'All Time');
        if ($filter === 'Daily') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($filter === 'Weekly') {
            $query->whereBetween('created_at', [Carbon::now()->subDays(7)->startOfDay(), Carbon::now()->endOfDay()]);
        } elseif ($filter === 'Monthly') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        } elseif ($filter === 'Custom' && $request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        $transactions = (clone $query)->with(['items.product', 'user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($trx) {
                return [
                    'id' => $trx->trx_code,
                    'date' => $trx->created_at ? Carbon::parse($trx->created_at)->format('Y-m-d \a\t H:i:s') : 'N/A',
                    'cashier' => $trx->user ? $trx->user->name : 'Unknown',
                    'total' => $trx->total_amount,
                    'payment_method' => ucfirst($trx->payment_method),
                    'items' => $trx->items->map(function ($item) {
                        return [
                            'name' => $item->product ? $item->product->name : 'Unknown Product',
                            'qty' => $item->qty,
                            'price' => $item->unit_price,
                            'subtotal' => $item->subtotal,
                        ];
                    })
                ];
            });
        
        $totalRevenue = (clone $query)->sum('total_amount') ?? 0;
        $transactionsCount = (clone $query)->count() ?? 0;
        
        $stats = [
            'totalRevenue' => $totalRevenue,
            'totalProfit' => $totalRevenue * 0.25, // Mocking profit as 25% of revenue
            'transactionsCount' => $transactionsCount,
            'avgTransaction' => $transactionsCount > 0 ? ($totalRevenue / $transactionsCount) : 0,
        ];

        $stockQuery = \App\Models\StockMovement::query();

        if ($filter === 'Daily') {
            $stockQuery->whereDate('created_at', Carbon::today());
        } elseif ($filter === 'Weekly') {
            $stockQuery->whereBetween('created_at', [Carbon::now()->subDays(7)->startOfDay(), Carbon::now()->endOfDay()]);
        } elseif ($filter === 'Monthly') {
            $stockQuery->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        } elseif ($filter === 'Custom' && $request->has('start_date') && $request->has('end_date')) {
            $stockQuery->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        $stockMovements = $stockQuery->with('product')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($movement) {
                return [
                    'id' => 'INV-' . str_pad($movement->id, 3, '0', STR_PAD_LEFT),
                    'date' => $movement->created_at ? Carbon::parse($movement->created_at)->format('Y-m-d') : 'N/A',
                    'type' => ucfirst($movement->type), // Incoming or Outgoing
                    'product_name' => $movement->product ? $movement->product->name : 'Unknown',
                    'unit' => $movement->product ? $movement->product->unit : '',
                    'qty' => $movement->qty,
                    'reference' => $movement->reference_id ?? '-',
                    'supplier' => $movement->supplier ?? '-',
                ];
            });

        return Inertia::render('Report', [
            'transactions' => $transactions,
            'stats' => $stats,
            'inventoryMovements' => $stockMovements,
            'filters' => [
                'filter' => $request->input('filter', 'All Time'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ]
        ]);
    }
}
