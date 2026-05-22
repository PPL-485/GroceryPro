<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $products = Product::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            return;
        }

        // Generate transactions for the last 30 days
        for ($i = 0; $i < 30; $i++) {
            // Generate 1-3 transactions per day
            $transactionCount = rand(1, 3);
            $date = Carbon::now()->subDays($i);

            for ($j = 0; $j < $transactionCount; $j++) {
                $user = $users->random();
                
                // Pick 1-4 random products
                $selectedProducts = $products->random(rand(1, 4));
                $totalAmount = 0;
                
                $trxItemsData = [];
                foreach ($selectedProducts as $product) {
                    $qty = rand(1, 5);
                    $subtotal = $product->sell_price * $qty;
                    $totalAmount += $subtotal;
                    
                    $trxItemsData[] = [
                        'product_id' => $product->id,
                        'qty' => $qty,
                        'unit_price' => $product->sell_price,
                        'subtotal' => $subtotal,
                    ];
                }

                // Generate Trx Code
                $datePrefix = $date->format('Ymd');
                $nextId = rand(1, 9999);
                $trxCode = 'TRX-' . $datePrefix . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                
                $paymentMethods = ['cash', 'qris'];
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
                
                $cashGiven = $paymentMethod === 'cash' ? (ceil($totalAmount / 10000) * 10000) : $totalAmount;
                if ($cashGiven < $totalAmount) {
                    $cashGiven = $totalAmount;
                }
                $change = $cashGiven - $totalAmount;

                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'trx_code' => $trxCode,
                    'payment_method' => $paymentMethod,
                    'total_amount' => $totalAmount,
                    'change' => $change,
                    'created_at' => $date->copy()->setTime(rand(8, 20), rand(0, 59), rand(0, 59)),
                ]);

                foreach ($trxItemsData as $itemData) {
                    $itemData['transaction_id'] = $transaction->id;
                    TransactionItem::create($itemData);
                    
                    // Decrement stock for realism (make sure it doesn't go below 0)
                    $product = Product::find($itemData['product_id']);
                    if ($product && $product->stock_qty >= $itemData['qty']) {
                        $product->decrement('stock_qty', $itemData['qty']);
                    }
                }
            }
        }
    }
}
