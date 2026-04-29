<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    $totalRevenue = \App\Models\Transaction::sum('total_amount');
    $totalProducts = \App\Models\Product::count();
    $transactionsToday = \App\Models\Transaction::whereDate('created_at', today())->count();
    $lowStockItemsCount = \App\Models\Product::whereColumn('stock_qty', '<=', 'min_stock')->count();
    
    $recentTransactions = \App\Models\Transaction::with('items')->orderBy('created_at', 'desc')->limit(4)->get()->map(function($trx) {
        return [
            'id' => $trx->trx_code,
            'customer' => 'Customer', // Or from user/customer if exists
            'items' => $trx->items->sum('qty'),
            'total' => $trx->total_amount,
            'payment_type' => $trx->payment_method,
        ];
    });

    $lowStockAlerts = \App\Models\Product::with('category')->whereColumn('stock_qty', '<=', 'min_stock')->limit(4)->get();

    return Inertia::render('Dashboard', [
        'stats' => [
            'totalRevenue' => $totalRevenue,
            'totalProducts' => $totalProducts,
            'transactionsToday' => $transactionsToday,
            'lowStockItemsCount' => $lowStockItemsCount,
        ],
        'recentTransactions' => $recentTransactions,
        'lowStockAlerts' => $lowStockAlerts,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/users', function () {
    return Inertia::render('User', [
        'users' => \App\Models\User::all(),
    ]);
})->middleware(['auth', 'verified'])->name('users.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/transactions', [\App\Http\Controllers\TransactionController::class, 'index'])->name('transactions');
    Route::post('/transactions', [\App\Http\Controllers\TransactionController::class, 'store'])->name('transactions.store');

    Route::get('/report', [\App\Http\Controllers\TransactionController::class, 'report'])->name('report');

    Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories');
    Route::post('/categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');

    Route::get('/goods', [\App\Http\Controllers\ProductController::class, 'index'])->name('products');
    Route::post('/goods', [\App\Http\Controllers\ProductController::class, 'store'])->name('products.store');
    Route::put('/goods/{product}', [\App\Http\Controllers\ProductController::class, 'update'])->name('products.update');
<<<<<<< Updated upstream
=======
    Route::delete('/goods/{product}', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('products.destroy');

    // Backup Database
    Route::get('/backup', [\App\Http\Controllers\BackupController::class, 'backup'])->name('backup');
>>>>>>> Stashed changes
    Route::post('/goods/add-stock', [\App\Http\Controllers\ProductController::class, 'addStock'])->name('products.add-stock');

    Route::get('/settings', function () {
        return Inertia::render('Settings');
    })->name('settings');
    Route::put('/users/{user}/role', function (\Illuminate\Http\Request $request, \App\Models\User $user) {
        $request->validate(['role' => 'required|in:admin,cashier']);
        $user->update(['role' => $request->role]);
        return back()->with('success', 'User role updated successfully.');
    })->name('users.update-role');
});

require __DIR__.'/auth.php';
