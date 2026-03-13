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

Route::middleware(['auth'])->group(function () {
    
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Items Management
    Route::get('items/generate-code', [ItemController::class, 'generateProductCode'])->name('items.generate-code');
    // Standard resource (creates items.index)
    Route::resource('items', ItemController::class); 

    Route::resource('categories', CategoryController::class);
    Route::resource('units', UnitController::class);
    
    // Transactions - Fixed for clarity
    Route::group(['prefix' => 'transactions', 'as' => 'transactions.'], function () {
        // This ensures the name is definitely 'transactions.index'
        Route::get('/', [TransactionController::class, 'index'])->name('index'); 
        
        Route::get('stock-in', [TransactionController::class, 'stockIn'])->name('stock-in');
        Route::post('stock-in/bulk', [TransactionController::class, 'store_bulk_in'])->name('store_bulk_in');
        Route::get('stock-out', [TransactionController::class, 'stockOut'])->name('stock-out');
        Route::post('stock-out/bulk', [TransactionController::class, 'store_bulk_out'])->name('store_bulk_out');
        
        Route::get('export/daily-in', [TransactionController::class, 'exportDailyIn'])->name('export-daily-in');
        Route::get('export/department', [TransactionController::class, 'exportByDepartment'])->name('export-by-department');
        Route::get('export/selected/items', [TransactionController::class, 'exportSelected'])->name('export-selected');
        Route::get('export/bulk/report', [TransactionController::class, 'exportBulkPdf'])->name('export-bulk');
        Route::get('export/{id}', [TransactionController::class, 'exportPdf'])->name('export-pdf');
    });

    Route::get('inventory/download-report', [ReportController::class, 'download'])->name('reports.download');

    Route::middleware('can:manage-users')->group(function () {
        Route::resource('users', UserController::class);
    });
});

require __DIR__.'/settings.php';