<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Api\v1\ItemController; 
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

Route::prefix('v1')->group(function () {
    Route::apiResource('items', ItemController::class);
    Route::apiResource('transactions', TransactionController::class);
});
