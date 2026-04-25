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
        Schema::table('products', function (Blueprint $table) {
            $table->bigInteger('buy_price')->change();
            $table->bigInteger('sell_price')->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->bigInteger('total_cost')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('buy_price')->change();
            $table->integer('sell_price')->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->integer('total_cost')->default(0)->change();
        });
    }
};
