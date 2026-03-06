<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController; 
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // 1. Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Reports
    Route::get('inventory/download-report', [ReportController::class, 'download'])->name('reports.download');

    // ITO LANG ANG DINAGDAG NATIN - Safe ito:
    Route::get('items/generate-code', [ItemController::class, 'generateProductCode'])->name('items.generate-code');

    // 3. Resource Routes 
    Route::resource('items', ItemController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('units', UnitController::class);
    Route::resource('transactions', TransactionController::class);
});

// Hayaan mo lang itong comment mo kung gumagana naman ang system mo
// Remove require auth.php since you don't have it
require __DIR__.'/settings.php';