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

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/users', function () {
        return Inertia::render('User', [
            'users' => \App\Models\User::all(),
        ]);
    })->name('users.index');

    Route::post('/users', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,cashier',
            'status' => 'required|in:active,inactive',
        ]);

        \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status,
        ]);

        return back()->with('success', 'User created successfully.');
    })->name('users.store');

    Route::get('/report', [\App\Http\Controllers\TransactionController::class, 'report'])->name('report');

    Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories');
    Route::post('/categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/goods', [\App\Http\Controllers\ProductController::class, 'index'])->name('products');
    Route::post('/goods', [\App\Http\Controllers\ProductController::class, 'store'])->name('products.store');
    Route::put('/goods/{product}', [\App\Http\Controllers\ProductController::class, 'update'])->name('products.update');
    Route::delete('/goods/{product}', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('products.destroy');

    // Backup & Restore Database
    Route::get('/backup', [\App\Http\Controllers\BackupController::class, 'backup'])->name('backup');
    Route::post('/restore', [\App\Http\Controllers\BackupController::class, 'restore'])->name('restore');

    Route::post('/goods/add-stock', [\App\Http\Controllers\ProductController::class, 'addStock'])->name('products.add-stock');

    Route::put('/users/{user}/role', function (\Illuminate\Http\Request $request, \App\Models\User $user) {
        $request->validate(['role' => 'required|in:admin,cashier']);
        $user->update(['role' => $request->role]);
        return back()->with('success', 'User role updated successfully.');
    })->name('users.update-role');

    Route::put('/users/{user}/status', function (\App\Models\User $user) {
        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);
        return back()->with('success', 'User status updated successfully.');
    })->name('users.update-status');

    Route::delete('/users/{user}', function (\App\Models\User $user) {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    })->name('users.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/transactions', [\App\Http\Controllers\TransactionController::class, 'index'])->name('transactions');
    Route::post('/transactions', [\App\Http\Controllers\TransactionController::class, 'store'])->name('transactions.store');

    Route::get('/settings', function () {
        return Inertia::render('Settings');
    })->name('settings');
});

require __DIR__.'/auth.php';
