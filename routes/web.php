<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{ItemController, CategoryController, UnitController, UserController, TransactionController, DashboardController, ReportController};

// Redirect root to dashboard
Route::get('/', fn() => redirect()->route('dashboard'))->name('home');

Route::middleware(['auth'])->group(function () {
    // ==========================================
    // 1. SHARED ACCESS (Makikita ng Viewer at iba pang roles)
    // ==========================================
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Inventory Items
    Route::get('items/generate-code', [ItemController::class, 'generateProductCode'])->name('web.items.generate-code');
    Route::resource('items', ItemController::class)->names('web.items'); 

    // BAGO: Inilabas natin ang listahan ng Transactions dito para mabasa ng Viewer
    Route::get('transactions', [TransactionController::class, 'index'])->name('web.transactions.index');


    // ==========================================
    // 2. PROTECTED OPERATIONS (Admin, Clerk, Custodian lang. Bawal ang Viewer)
    // ==========================================
    Route::middleware('can:manage-inventory')->group(function () {
        
        // Static Resources
        Route::resource('categories', CategoryController::class);
        Route::resource('units', UnitController::class);
        
        // Transactions Actions (Dito yung mga bawal sa Viewer)
        Route::group(['prefix' => 'transactions', 'as' => 'web.transactions.'], function () {
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

        // Reports
        Route::get('inventory/download-report', [ReportController::class, 'download'])->name('reports.download');
    });


    // ==========================================
    // 3. ADMIN ONLY ACCESS
    // ==========================================
    Route::middleware('can:manage-users')->group(function () {
        Route::resource('users', UserController::class);
    });
});

require __DIR__.'/settings.php';