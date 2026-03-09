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
    
    // 1. PUBLIC ACCESS (Authenticated only)
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('items', [ItemController::class, 'index'])->name('items.index');
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');

    // 2. INVENTORY MANAGEMENT (Admin, Clerk, Custodian)
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
        
        // =========================================================
        // NEW TRANSACTION SYSTEM (Stock In / Stock Out / Bulk)
        // =========================================================
        // IMPORTANT: Specific routes MUST come before the Resource routes
        Route::controller(TransactionController::class)->group(function () {
            
            // Stock In Routes
            Route::get('transactions/stock-in', 'stockIn')->name('transactions.stock-in');
            Route::post('transactions/stock-in', 'store')->name('transactions.store');

            // Stock Out Routes
            Route::get('transactions/stock-out', 'stockOut')->name('transactions.stock-out');
            Route::post('transactions/stock-out/bulk', 'store_bulk_out')->name('transactions.store_bulk_out');

            // Export & PDF Group
            Route::prefix('transactions/export')->group(function () {
                // Route for Departmental Export per Supervisor requirement
                Route::get('/department', 'exportByDepartment')->name('transactions.export-department');
                Route::get('/selected/items', 'exportSelected')->name('transactions.export-selected');
                Route::get('/bulk/report', 'exportBulkPdf')->name('transactions.export-bulk');
                Route::get('/{id}', 'exportPdf')->name('transactions.export-pdf');
            });
        });

        // Resource route defined AFTER specific paths to avoid "stock-in" being treated as an {id}
        Route::resource('transactions', TransactionController::class)->except(['index', 'show', 'destroy', 'store']);
    });

    // 3. WILDCARD SHOW ROUTE
    Route::get('items/{item}', [ItemController::class, 'show'])->name('items.show');

    // 4. HIGH-LEVEL PERMISSIONS (Admin, Custodian)
    Route::middleware('can:delete-inventory')->group(function () {
        Route::delete('items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
        // Reports for School Inventory Management
        Route::get('inventory/download-report', [ReportController::class, 'download'])->name('reports.download');
    });
});

require __DIR__.'/settings.php';