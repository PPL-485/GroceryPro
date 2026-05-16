<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public $product;

    /**
     * Create a new notification instance.
     */
    public function __construct(\App\Models\Product $product)
    {
        $this->product = $product;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Alert: Low Stock for ' . $this->product->name)
                    ->line('The stock for ' . $this->product->name . ' has fallen below the minimum threshold.')
                    ->line('Current Stock: ' . $this->product->stock_qty)
                    ->line('Minimum Required: ' . $this->product->min_stock)
                    ->action('View Inventory', url('/goods'))
                    ->line('Please restock this item as soon as possible.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'stock_qty' => $this->product->stock_qty,
            'min_stock' => $this->product->min_stock,
        ];
    }
}
