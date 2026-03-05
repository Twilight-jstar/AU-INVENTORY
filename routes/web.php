<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController; 
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

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Reports Route
    Route::get('/dashboard/download-report', [ReportController::class, 'download'])->name('reports.download');

    // Inventory Management Resource Routes
    Route::resource('items', ItemController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('units', UnitController::class);
    Route::resource('transactions', TransactionController::class);

    //Delete routes for categories and units
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');
});

require __DIR__.'/settings.php';
