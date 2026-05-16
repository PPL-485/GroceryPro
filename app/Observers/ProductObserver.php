<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Notification;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Check if stock_qty was updated and has fallen to or below min_stock
        // Also ensure it was previously above min_stock to prevent duplicate alerts
        if ($product->isDirty('stock_qty')) {
            $originalStock = $product->getOriginal('stock_qty');
            
            if ($product->stock_qty <= $product->min_stock && $originalStock > $product->min_stock) {
                // Dispatch notification to admins
                $admins = User::where('role', 'admin')->get();
                Notification::send($admins, new LowStockNotification($product));
            }
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
