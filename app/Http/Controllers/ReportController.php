<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $query = Transaction::query();

        if (auth()->user()->role === 'cashier') {
            $query->where('user_id', auth()->id());
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

        $stockMovements = \App\Models\StockMovement::with('product')
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
        ]);
    }
}
