<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\Api\v1\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes - ALF Inventory Management System
|--------------------------------------------------------------------------
*/

// 1. Public Auth Route
Route::post('/login', [AuthController::class, 'login']);

// 2. Protected Routes (Token Required)
Route::middleware('auth:sanctum')->group(function () {

    // --- SHARED ACCESS (All Roles) ---
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('items', [ItemController::class, 'index']);
    Route::get('transactions', [TransactionController::class, 'index']);

    // --- INVENTORY OPERATIONS (Admin, Clerk, Custodian) ---
    Route::middleware('can:manage-inventory')->group(function () {
        
        // Item Management
        Route::get('items/generate-code', [ItemController::class, 'generateProductCode']);
        Route::apiResource('items', ItemController::class)->except(['index']);
        
        // Helpers
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('units', UnitController::class);

        // Stock Operations (Single & Bulk)
        Route::post('stock-in', [StockInController::class, 'store']);
        Route::post('stock-out', [StockOutController::class, 'store']);
        
        Route::controller(TransactionController::class)->group(function () {
            Route::post('transactions/stock-in/bulk', 'store_bulk_in');
            Route::post('transactions/stock-out/bulk', 'store_bulk_out');
        });
    });

    // --- USER MANAGEMENT (Admin Only) ---
    Route::middleware('can:manage-users')->group(function () {
        Route::apiResource('users', UserController::class);
    });
    
    // Auth & Identity Helpers
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn (Request $request) => $request->user());
});