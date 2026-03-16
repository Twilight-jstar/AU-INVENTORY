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

Route::middleware(['auth', 'verified'])->group(function () {
    
    // ==========================================
    // 1. SHARED ACCESS (All Roles)
    // ==========================================
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('items', [ItemController::class, 'index'])->name('items.index');
    
    // BAGO: TINANGGAL NA DITO YUNG TRANSACTIONS ROUTE PARA DI MA-ACCESS NG VIEWER

    // ==========================================
    // 2. INVENTORY OPERATIONS (Admin, Clerk, Custodian)
    // ==========================================
    Route::middleware('can:manage-inventory')->group(function () {

        // BAGO: NILIPAT DITO YUNG TRANSACTIONS ROUTE 
        // Para ang may access lang dito ay yung mga may 'manage-inventory' permission
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');

        // --- ITEM MANAGEMENT ---
        // Static routes first to avoid 404s
        Route::get('items/generate-code', [ItemController::class, 'generateProductCode'])->name('items.generate-code');
        Route::get('items/create', [ItemController::class, 'create'])->name('items.create');
        Route::post('items', [ItemController::class, 'store'])->name('items.store');
        
        // Wildcard routes
        Route::get('items/{item}', [ItemController::class, 'show'])->name('items.show');
        Route::get('items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
        Route::put('items/{item}', [ItemController::class, 'update'])->name('items.update');
        Route::delete('items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');

        // --- HELPERS ---
        Route::resource('categories', CategoryController::class);
        Route::resource('units', UnitController::class);
        
        // --- STOCK IN / OUT ---
        Route::controller(TransactionController::class)->group(function () {
            Route::get('transactions/stock-in', 'stockIn')->name('transactions.stock-in');
            Route::post('transactions/stock-in/bulk', 'store_bulk_in')->name('transactions.store_bulk_in');
            Route::get('transactions/stock-out', 'stockOut')->name('transactions.stock-out');
            Route::post('transactions/stock-out/bulk', 'store_bulk_out')->name('transactions.store_bulk_out');
        });

        // --- EXPORTS ---
        Route::get('inventory/download-report', [ReportController::class, 'download'])->name('reports.download');
        
        Route::controller(TransactionController::class)->prefix('transactions/export')->group(function () {
            Route::get('/daily-in', 'exportDailyIn')->name('transactions.export-daily-in');
            Route::get('/department', 'exportByDepartment')->name('transactions.export-by-department');
            Route::get('/selected/items', 'exportSelected')->name('transactions.export-selected');
            Route::get('/bulk/report', 'exportBulkPdf')->name('transactions.export-bulk');
            // This MUST be the last route in the group
            Route::get('/{id}', 'exportPdf')->name('transactions.export-pdf');
        });
    });

    // ==========================================
    // 3. USER MANAGEMENT (Admin Only)
    // ==========================================
    Route::middleware('can:manage-users')->group(function () {
        Route::resource('users', UserController::class);
    });
});

require __DIR__.'/settings.php';