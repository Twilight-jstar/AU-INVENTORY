<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController; 
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

// Only 'auth' is required. 'verified' is removed to stop the 403 errors.
Route::middleware(['auth'])->group(function () {
    
    // Shared Access
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('items', [ItemController::class, 'index'])->name('items');
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions');

    // Inventory Management Group
    Route::prefix('items')->name('items.')->group(function () {
        Route::get('generate-code', [ItemController::class, 'generateProductCode'])->name('generate-code');
        Route::get('create', [ItemController::class, 'create'])->name('create');
        Route::post('/', [ItemController::class, 'store'])->name('store');
        Route::get('{item}', [ItemController::class, 'show'])->name('show');
        Route::get('{item}/edit', [ItemController::class, 'edit'])->name('edit');
        Route::put('{item}', [ItemController::class, 'update'])->name('update');
        Route::delete('{item}', [ItemController::class, 'destroy'])->name('destroy');
    });

    // Helpers
    Route::resource('categories', CategoryController::class);
    Route::resource('units', UnitController::class);
    
    // Transactions
    Route::controller(TransactionController::class)->prefix('transactions')->name('transactions.')->group(function () {
        Route::get('stock-in', 'stockIn')->name('stock-in');
        Route::post('stock-in/bulk', 'store_bulk_in')->name('store_bulk_in');
        Route::get('stock-out', 'stockOut')->name('stock-out');
        Route::post('stock-out/bulk', 'store_bulk_out')->name('store_bulk_out');
        
        // Exports
        Route::get('export/daily-in', 'exportDailyIn')->name('export-daily-in');
        Route::get('export/department', 'exportByDepartment')->name('export-by-department');
        Route::get('export/selected/items', 'exportSelected')->name('export-selected');
        Route::get('export/bulk/report', 'exportBulkPdf')->name('export-bulk');
        Route::get('export/{id}', 'exportPdf')->name('export-pdf');
    });

    Route::get('inventory/download-report', [ReportController::class, 'download'])->name('reports.download');

    // Admin Only: User Management
    Route::middleware('can:manage-users')->group(function () {
        Route::resource('users', UserController::class);
    });
});

require __DIR__.'/settings.php';