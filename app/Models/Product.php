<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'unit',
        'image_url',
        'buy_price',
        'sell_price',
        'stock_qty',
        'min_stock',
        'status',
    ];

    /**
     * A product belongs to a category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * A product has many stock movements.
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
