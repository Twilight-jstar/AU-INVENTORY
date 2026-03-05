<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController; // 1. Add this import
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    
    // 2. Change this line from Route::inertia to Route::get
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Reports Route
    Route::get('/dashboard/download-report', [ReportController::class, 'download'])->name('reports.download');

    // Inventory Management Resource Routes
    Route::resource('items', ItemController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('units', UnitController::class);
    Route::resource('transactions', TransactionController::class);
});

require __DIR__.'/settings.php';
