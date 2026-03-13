<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController; 
use App\Http\Controllers\ReportController;

// 1. Root Redirection
Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth'])->group(function () {
    
    // 2. Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 3. Items Management
    // IMPORTANT: Custom routes MUST be defined BEFORE the resource routes
    Route::get('items/generate-code', [ItemController::class, 'generateProductCode'])->name('items.generate-code');
    
    // Explicitly define the index to match your sidebar name
    Route::get('items', [ItemController::class, 'index'])->name('items');
    
    // The resource handles: create, store, show, edit, update, destroy
    Route::resource('items', ItemController::class)->except(['index']);

    // 4. Categories & Units
    Route::resource('categories', CategoryController::class);
    Route::resource('units', UnitController::class);
    
    // 5. Transactions
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('stock-in', [TransactionController::class, 'stockIn'])->name('stock-in');
        Route::post('stock-in/bulk', [TransactionController::class, 'store_bulk_in'])->name('store_bulk_in');
        Route::get('stock-out', [TransactionController::class, 'stockOut'])->name('stock-out');
        Route::post('stock-out/bulk', [TransactionController::class, 'store_bulk_out'])->name('store_bulk_out');
        
        // Export Routes
        Route::get('export/daily-in', [TransactionController::class, 'exportDailyIn'])->name('export-daily-in');
        Route::get('export/department', [TransactionController::class, 'exportByDepartment'])->name('export-by-department');
        Route::get('export/selected/items', [TransactionController::class, 'exportSelected'])->name('export-selected');
        Route::get('export/bulk/report', [TransactionController::class, 'exportBulkPdf'])->name('export-bulk');
        Route::get('export/{id}', [TransactionController::class, 'exportPdf'])->name('export-pdf');
    });

    // 6. Reports
    Route::get('inventory/download-report', [ReportController::class, 'download'])->name('reports.download');

    // 7. Admin Only
    Route::middleware('can:manage-users')->group(function () {
        Route::resource('users', UserController::class);
    });
});

require __DIR__.'/settings.php';