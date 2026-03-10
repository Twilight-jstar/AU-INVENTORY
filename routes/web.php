<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController; 
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    

// 0. ADMIN ONLY - USER MANAGEMENT (Dito natin isiningit sa pinakataas ng group)
    Route::middleware(['role:Admin'])->group(function () {
        Route::resource('users', UserController::class);
    });
    // 1. PUBLIC ACCESS
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('items', [ItemController::class, 'index'])->name('items.index');
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');

    // 2. INVENTORY MANAGEMENT
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
        
        // Transaction System
        Route::controller(TransactionController::class)->group(function () {
            
            // Stock In/Out - PROTECTED BY ROLE MIDDLEWARE
            Route::middleware(['role:Custodian,Clerk'])->group(function () {
                Route::get('transactions/stock-in', 'stockIn')->name('transactions.stock-in');
                Route::post('transactions/stock-in', 'store')->name('transactions.store');
                Route::get('transactions/stock-out', 'stockOut')->name('transactions.stock-out');
                Route::post('transactions/stock-out/bulk', 'store_bulk_out')->name('transactions.store_bulk_out');
            }); // <-- Make sure nandito itong closing bracket na 'to

            // Export & PDF Group
            Route::prefix('transactions/export')->group(function () {
                // The new Daily In Route
                Route::get('/daily-in', 'exportDailyIn')->name('transactions.export-daily-in');
                
                Route::get('/department', 'exportByDepartment')->name('transactions.export-department');
                Route::get('/selected/items', 'exportSelected')->name('transactions.export-selected');
                Route::get('/bulk/report', 'exportBulkPdf')->name('transactions.export-bulk');
                Route::get('/{id}', 'exportPdf')->name('transactions.export-pdf');
            });
        });

        Route::resource('transactions', TransactionController::class)->except(['index', 'show', 'destroy', 'store']);
    });

    // 3. WILDCARD SHOW ROUTE
    Route::get('items/{item}', [ItemController::class, 'show'])->name('items.show');

    // 4. HIGH-LEVEL PERMISSIONS
    Route::middleware('can:delete-inventory')->group(function () {
        Route::delete('items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
        Route::get('inventory/download-report', [ReportController::class, 'download'])->name('reports.download');
    });
});

require __DIR__.'/settings.php';