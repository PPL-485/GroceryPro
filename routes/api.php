<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BackupController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Backup API Routes (protected with auth)
Route::middleware('auth:sanctum')->group(function () {
    Route::controller(BackupController::class)->group(function () {
        Route::get('/backups', 'index')->name('backups.index');
        Route::post('/backups', 'create')->name('backups.create');
        Route::get('/backups/stats', 'stats')->name('backups.stats');
        Route::get('/backups/{filename}/download', 'download')->name('backups.download');
        Route::delete('/backups/{filename}', 'destroy')->name('backups.destroy');
    });
});
