<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * Display the POS transactions view.
     */
    public function index()
    {
        $categories = Category::all();
        $products = Product::with('category')->get();

        return Inertia::render('Transactions', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created transaction in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,qris',
            'change' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Generate Trx Code
            $datePrefix = date('Ymd');
            $lastTrx = Transaction::whereDate('created_at', date('Y-m-d'))->orderBy('id', 'desc')->first();
            $nextId = $lastTrx ? ((int) substr($lastTrx->trx_code, -4)) + 1 : 1;
            $trxCode = 'TRX-' . $datePrefix . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            // Create Transaction
            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'trx_code' => $trxCode,
                'payment_method' => $validated['payment_method'],
                'total_amount' => (int) round($validated['total_amount']),
                'change' => (int) round($validated['change']),
            ]);

            // Create Items & Update Stock
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                
                if ($product->stock_qty < $item['qty']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $product->decrement('stock_qty', $item['qty']);

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'unit_price' => (int) round($item['unit_price']),
                    'subtotal' => (int) round($item['subtotal']),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Transaction completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Transaction Error: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function report()
    {
        return Inertia::render('Report');
    }
}
