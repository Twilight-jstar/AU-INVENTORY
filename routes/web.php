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
    
    // 1. PUBLIC ACCESS
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('items', [ItemController::class, 'index'])->name('items.index');
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');

    // 2. INVENTORY MANAGEMENT (Admin, Clerk, Custodian)
    Route::middleware('can:manage-inventory')->group(function () {
        // Explicitly define 'create' and 'edit' BEFORE the wildcard to prevent 404s
        Route::get('items/create', [ItemController::class, 'create'])->name('items.create');
        Route::post('items', [ItemController::class, 'store'])->name('items.store');
        Route::get('items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
        Route::put('items/{item}', [ItemController::class, 'update'])->name('items.update');

        Route::resource('categories', CategoryController::class);
        Route::resource('units', UnitController::class);
        Route::resource('transactions', TransactionController::class)->except(['index', 'show', 'destroy']);
    });

    // 3. WILDCARD SHOW ROUTE (Must be below 'items/create')
    Route::get('items/{item}', [ItemController::class, 'show'])->name('items.show');

    // 4. HIGH-LEVEL PERMISSIONS (Admin, Custodian)
    Route::middleware('can:delete-inventory')->group(function () {
        Route::delete('items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
        Route::get('inventory/download-report', [ReportController::class, 'download'])->name('reports.download');
    });
});

require __DIR__.'/settings.php';