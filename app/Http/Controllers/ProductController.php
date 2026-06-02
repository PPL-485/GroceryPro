<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->get();
        $categories = Category::all();
        
        // Get incoming stock history (type = 'in')
        $stockMovements = StockMovement::with('product')
            ->where('type', 'in')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Products', [
            'products' => $products,
            'categories' => $categories,
            'stockMovements' => $stockMovements,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:products,name', 'regex:/[a-zA-Z]/'],
            'category_id' => 'required|exists:categories,id',
            'stock_qty' => 'required|numeric|gt:0',
            'min_stock' => 'required|numeric|gt:0',
            'unit' => 'nullable|string|max:50',
            'buy_price' => 'required|numeric|gt:0',
            'sell_price' => 'required|numeric|gt:0',
            'supplier' => ['nullable', 'string', 'max:100', 'regex:/[a-zA-Z]/'],
        ], [
            'name.regex' => 'Product name must contain at least one letter.',
            'supplier.regex' => 'Supplier name must contain at least one letter.',
        ]);

        // Generate SKU
        $lastProduct = Product::orderBy('id', 'desc')->first();
        $nextId = $lastProduct ? $lastProduct->id + 1 : 1;
        $sku = 'PRD-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        $product = Product::create([
            'sku' => $sku,
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'unit' => $validated['unit'] ?? 'pcs',
            'stock_qty' => $validated['stock_qty'],
            'buy_price' => $validated['buy_price'],
            'sell_price' => $validated['sell_price'],
            'min_stock' => $validated['min_stock'],
            'status' => 'active',
        ]);

        // Create initial stock movement if stock > 0
        if ($validated['stock_qty'] > 0) {
            $movement = StockMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => 'in',
                'qty' => $validated['stock_qty'],
                'supplier' => $validated['supplier'] ?? null,
                'total_cost' => $validated['buy_price'] * $validated['stock_qty'],
                'created_at' => now(),
            ]);
            
            $movement->update([
                'reference_id' => 'INV-IN-' . str_pad($movement->id, 4, '0', STR_PAD_LEFT)
            ]);
        }

        return redirect()->back()->with('success', 'Product added successfully.');
    }

    public function addStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|numeric|gt:0',
            'supplier' => 'nullable|string|max:100',
            'date_received' => 'required|date',
            'total_cost' => 'required|numeric|gt:0',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        
        // Update product stock
        $product->increment('stock_qty', $validated['qty']);

        // Create stock movement record
        $movement = StockMovement::create([
            'product_id' => $validated['product_id'],
            'user_id' => auth()->id(),
            'type' => 'in',
            'qty' => $validated['qty'],
            'supplier' => $validated['supplier'],
            'total_cost' => $validated['total_cost'],
            'created_at' => $validated['date_received'],
        ]);

        $movement->update([
            'reference_id' => 'INV-IN-' . str_pad($movement->id, 4, '0', STR_PAD_LEFT)
        ]);

        return redirect()->back()->with('success', 'Stock added successfully.');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:products,name,' . $product->id, 'regex:/[a-zA-Z]/'],
            'category_id' => 'required|exists:categories,id',
            'unit' => 'nullable|string|max:50',
            'buy_price' => 'required|numeric|gt:0',
            'sell_price' => 'required|numeric|gt:0',
            'min_stock' => 'required|numeric|gt:0',
            'stock_qty' => 'required|numeric|gt:0',
        ], [
            'name.regex' => 'Product name must contain at least one letter.',
        ]);

        $product->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'unit' => $validated['unit'] ?? 'pcs',
            'buy_price' => $validated['buy_price'],
            'sell_price' => $validated['sell_price'],
            'min_stock' => $validated['min_stock'],
            'stock_qty' => $validated['stock_qty'],
        ]);

        return redirect()->back()->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Set product_id to NULL in transaction_items (keep transaction history)
        \App\Models\TransactionItem::where('product_id', $product->id)
            ->update(['product_id' => null]);

        // Delete related stock movements
        $product->stockMovements()->delete();
        
        // Delete the product
        $product->delete();

        return redirect()->back()->with('success', 'Product deleted successfully.');
    }
}
