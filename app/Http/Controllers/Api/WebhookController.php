<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function midtrans(Request $request)
    {
        // Set your Merchant Server Key
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);

        try {
            $notification = new \Midtrans\Notification();

            $transactionStatus = $notification->transaction_status;
            $orderId = $notification->order_id;
            $fraudStatus = $notification->fraud_status;

            // Log the notification for debugging
            Log::info("Midtrans Notification: Order ID $orderId, Status $transactionStatus");

            DB::beginTransaction();

            // Find and lock the transaction by trx_code to prevent race conditions with frontend check
            $transaction = Transaction::with('items')->where('trx_code', $orderId)->lockForUpdate()->first();

            if (!$transaction) {
                DB::rollBack();
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            // Only process if the transaction is pending
            if ($transaction->status !== 'pending') {
                DB::commit();
                return response()->json(['message' => 'Transaction already processed'], 200);
            }

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    // TODO set transaction status on your database to 'challenge'
                    // and response with 200 OK
                } else if ($fraudStatus == 'accept') {
                    $this->processSuccess($transaction);
                }
            } else if ($transactionStatus == 'settlement') {
                $this->processSuccess($transaction);
            } else if ($transactionStatus == 'cancel' ||
              $transactionStatus == 'deny' ||
              $transactionStatus == 'expire') {
                $transaction->update(['status' => 'failed']);
            } else if ($transactionStatus == 'pending') {
                // DO NOTHING
            }

            DB::commit();

            return response()->json(['message' => 'Webhook received']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Midtrans Webhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error handling webhook'], 500);
        }
    }

    private function processSuccess(Transaction $transaction)
    {
        $transaction->update(['status' => 'paid']);

        // Reduce stock and create stock movements
        foreach ($transaction->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                // If we allow negative stock, we just decrement. 
                // Or maybe the stock was verified during checkout (pending state).
                $product->decrement('stock_qty', $item->qty);

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'user_id' => $transaction->user_id, // Assuming user_id is the cashier
                    'type' => 'out',
                    'qty' => $item->qty,
                    'reference_id' => $transaction->trx_code,
                    'supplier' => 'Customer (POS QRIS)',
                    'total_cost' => $item->subtotal,
                    'created_at' => now(),
                ]);
            }
        }
    }
}

