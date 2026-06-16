<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
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

            // Create Transaction
            $transaction = $this->createTransactionWithUniqueCode([
                'user_id' => auth()->id(),
                'payment_method' => $validated['payment_method'],
                'total_amount' => (int) round($validated['total_amount']),
                'change' => (int) round($validated['change']),
                'status' => $validated['payment_method'] === 'cash' ? 'paid' : 'pending',
            ]);

            // Create Items & Update Stock
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                
                if ($product->stock_qty < $item['qty']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'unit_price' => (int) round($item['unit_price']),
                    'subtotal' => (int) round($item['subtotal']),
                ]);

                if ($validated['payment_method'] === 'cash') {
                    $product->decrement('stock_qty', $item['qty']);

                    // Create Stock Movement
                    StockMovement::create([
                        'product_id' => $item['product_id'],
                        'user_id' => auth()->id(),
                        'type' => 'out',
                        'qty' => $item['qty'],
                        'reference_id' => $transaction->trx_code,
                        'supplier' => 'Customer (POS)',
                        'total_cost' => (int) round($item['subtotal']),
                        'created_at' => now(),
                    ]);
                }
            }



            DB::commit();

            if ($validated['payment_method'] === 'qris') {
                // Set your Merchant Server Key
                \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
                \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
                \Midtrans\Config::$isSanitized = true;
                \Midtrans\Config::$is3ds = true;

                $params = array(
                    'transaction_details' => array(
                        'order_id' => $transaction->trx_code,
                        'gross_amount' => $transaction->total_amount,
                    ),
                    'enabled_payments' => array('other_qris'),
                    'customer_details' => array(
                        'first_name' => 'Customer',
                        'last_name' => 'POS',
                        'email' => 'customer@pos.local',
                        'phone' => '08111222333',
                    ),
                );

                $snapToken = \Midtrans\Snap::getSnapToken($params);
                $transaction->update(['snap_token' => $snapToken]);

                return response()->json([
                    'snap_token' => $snapToken,
                    'trx_code' => $transaction->trx_code
                ]);
            }

            return redirect()->back()->with('success', 'Transaction completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Transaction Error: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 400);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    private function createTransactionWithUniqueCode(array $attributes): Transaction
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return Transaction::create([
                    ...$attributes,
                    'trx_code' => $this->generateTrxCode(),
                ]);
            } catch (QueryException $e) {
                if (($e->errorInfo[1] ?? null) !== 1062) {
                    throw $e;
                }
            }
        }

        throw new \Exception('Failed to generate a unique transaction code. Please try again.');
    }

    private function generateTrxCode(): string
    {
        $datePrefix = date('Ymd');
        $lastTrx = Transaction::whereDate('created_at', date('Y-m-d'))
            ->lockForUpdate()
            ->orderBy('id', 'desc')
            ->first();

        $nextId = $lastTrx ? ((int) substr($lastTrx->trx_code, -4)) + 1 : 1;

        return 'TRX-' . $datePrefix . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    public function report()
    {
        return Inertia::render('Report');
    }

    public function markPaid(Request $request)
    {
        $trxCode = $request->input('order_id');
        \Illuminate\Support\Facades\Log::info('markPaid called for order_id: ' . $trxCode);
        
        $transaction = Transaction::with('items')->where('trx_code', $trxCode)->first();
        
        if (!$transaction) {
            return response()->json(['message' => 'Invalid transaction'], 400);
        }

        if ($transaction->status === 'paid') {
            return response()->json(['message' => 'Success']);
        }
        
        if ($transaction->status !== 'pending') {
            return response()->json(['message' => 'Transaction cannot be paid'], 400);
        }
        
        // Securely check status from Midtrans API directly to prevent spoofing
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        
        try {
            $status = \Midtrans\Transaction::status($trxCode);
            \Illuminate\Support\Facades\Log::info('Midtrans status for ' . $trxCode . ': ' . $status->transaction_status);
            
            if ($status->transaction_status == 'settlement' || $status->transaction_status == 'capture') {
                DB::beginTransaction();
                
                // Re-fetch and lock transaction to prevent race conditions with webhook
                $transaction = Transaction::with('items')->where('id', $transaction->id)->lockForUpdate()->first();
                
                if ($transaction->status === 'paid') {
                    DB::commit();
                    return response()->json(['message' => 'Success']);
                }

                $transaction->update(['status' => 'paid']);
                foreach ($transaction->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->decrement('stock_qty', $item->qty);
                        StockMovement::create([
                            'product_id' => $item->product_id,
                            'user_id' => $transaction->user_id,
                            'type' => 'out',
                            'qty' => $item->qty,
                            'reference_id' => $transaction->trx_code,
                            'supplier' => 'Customer (POS QRIS)',
                            'total_cost' => $item->subtotal,
                            'created_at' => now(),
                        ]);
                    }
                }
                DB::commit();
                return response()->json(['message' => 'Success']);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Midtrans API Error (markPaid): ' . $e->getMessage());
            return response()->json(['message' => 'Midtrans API Error: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Transaction not paid yet according to Midtrans'], 400);
    }
}
