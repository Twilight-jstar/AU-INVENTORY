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
    
    // 1. PUBLIC & SHARED ACCESS
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('items', [ItemController::class, 'index'])->name('items.index');
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('items/{item}', [ItemController::class, 'show'])->name('items.show');

    // 2. EXPORT SYSTEM (PINALITAN NG HYPHEN '-' PARA MAG-MATCH SA FRONTEND)
    Route::controller(TransactionController::class)->group(function () {
        Route::prefix('transactions/export')->group(function () {
            // Pinalitan ang '_' ng '-' para hindi mag-error ang Ziggy sa console
            Route::get('/daily-in', 'exportDailyIn')->name('transactions.export-daily-in');
            Route::get('/department', 'exportByDepartment')->name('transactions.export-by-department');
            Route::get('/selected/items', 'exportSelected')->name('transactions.export-selected');
            Route::get('/bulk/report', 'exportBulkPdf')->name('transactions.export-bulk');
            Route::get('/{id}', 'exportPdf')->name('transactions.export-pdf');
        });
    });

    // 3. INVENTORY MANAGEMENT (Restricted to authorized users only)
    Route::middleware('can:manage-inventory')->group(function () {

        // Item Management
        Route::get('items/generate-code', [ItemController::class, 'generateProductCode'])->name('items.generate-code');
        Route::get('items/create', [ItemController::class, 'create'])->name('items.create');
        Route::post('items', [ItemController::class, 'store'])->name('items.store');
        Route::get('items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
        Route::put('items/{item}', [ItemController::class, 'update'])->name('items.update');

        // Helpers
        Route::resource('categories', CategoryController::class);
        Route::resource('units', UnitController::class);
        
        // Transaction Entries
        Route::controller(TransactionController::class)->group(function () {
            // Stock In
            Route::get('transactions/stock-in', 'stockIn')->name('transactions.stock-in');
            // Pinanatiling underscore ang 'store_bulk_in' dahil kadalasan ito ay nasa form @submit, hindi sa direct URL export
            Route::post('transactions/stock-in/bulk', 'store_bulk_in')->name('transactions.store_bulk_in');
            
            // Stock Out
            Route::get('transactions/stock-out', 'stockOut')->name('transactions.stock-out');
            Route::post('transactions/stock-out/bulk', 'store_bulk_out')->name('transactions.store_bulk_out');
        });

        // Remaining resource methods
        Route::resource('transactions', TransactionController::class)->except(['index', 'show', 'destroy', 'store']);
    });

    // 4. ADMIN / DELETION PERMISSIONS
    Route::middleware('can:delete-inventory')->group(function () {
        Route::delete('items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
        Route::get('inventory/download-report', [ReportController::class, 'download'])->name('reports.download');
    });
});

require __DIR__.'/settings.php';