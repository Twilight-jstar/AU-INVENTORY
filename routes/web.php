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

    // 1. Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Reports
    Route::get('inventory/download-report', [ReportController::class, 'download'])->name('reports.download');

    // This automatically handles index, create, store, show, edit, update, and DESTROY
    // 3. Resource Routes 
    Route::resource('items', ItemController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('units', UnitController::class);
    Route::resource('transactions', TransactionController::class);

});

require __DIR__.'/settings.php';
