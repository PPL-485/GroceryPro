<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    public static function getMockTransactions()
    {
        return [
            [
                'id' => 'TRX-2026-0127',
                'date' => '2026-03-27 at 14:35:22',
                'cashier' => 'Andi',
                'total' => 83000,
                'payment_method' => 'Cash',
                'items' => [
                    ['name' => 'Indomie Goreng', 'qty' => 10, 'price' => 3000, 'subtotal' => 30000],
                    ['name' => 'Aqua 600ml', 'qty' => 6, 'price' => 3500, 'subtotal' => 21000],
                    ['name' => 'Minyak Goreng 2L', 'qty' => 1, 'price' => 32000, 'subtotal' => 32000],
                ]
            ],
            [
                'id' => 'TRX-2026-0126',
                'date' => '2026-03-27 at 13:20:15',
                'cashier' => 'Andi',
                'total' => 195000,
                'payment_method' => 'QRIS',
                'items' => [
                    ['name' => 'Beras Premium 5kg', 'qty' => 2, 'price' => 75000, 'subtotal' => 150000],
                    ['name' => 'Gula Pasir 1kg', 'qty' => 3, 'price' => 15000, 'subtotal' => 45000],
                ]
            ]
        ];
    }
}
