<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->string('sku', 50)->unique();
            $table->string('name', 100);
            $table->integer('buy_price');
            $table->integer('sell_price');
            $table->integer('stock_qty')->default(0);
            $table->integer('min_stock')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->datetime('created_at')->useCurrent();
            
            $table->index('category_id');
            $table->index('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};