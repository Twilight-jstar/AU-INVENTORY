<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{ItemController, CategoryController, UnitController, UserController, TransactionController, DashboardController, ReportController};

// Redirect root to dashboard
Route::get('/', fn() => redirect()->route('dashboard'))->name('home');

Route::middleware(['auth'])->group(function () {
    // Dashboard (Lahat pwedeng makakita)
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Inventory Items (Resource with custom names - Lahat pwede makakita ng listahan)
    Route::get('items/generate-code', [ItemController::class, 'generateProductCode'])->name('web.items.generate-code');
    Route::resource('items', ItemController::class)->names('web.items'); 

    // Static Resources (Admin, Clerk, Custodian lang - BAGO: Binalik ko ang proteksyon)
    Route::middleware('can:manage-inventory')->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::resource('units', UnitController::class);
        
        // Transactions Group (BAGO: Nasa loob na ito ng manage-inventory para di mapasok ng Viewer)
        Route::group(['prefix' => 'transactions', 'as' => 'web.transactions.'], function () {
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

        // Reports (BAGO: Kinulong na rin para hindi maka-download ang Viewer)
        Route::get('inventory/download-report', [ReportController::class, 'download'])->name('reports.download');
    });

    // Admin Only Access
    Route::middleware('can:manage-users')->group(function () {
        Route::resource('users', UserController::class);
    });
});

require __DIR__.'/settings.php';