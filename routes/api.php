<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\ItemController; 
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Auth
Route::post('/login', [AuthController::class, 'login']);

// Items API
Route::apiResource('items', ItemController::class);
Route::get('/productCode', [ItemController::class, 'productCode'])->name('items.product-code');
Route::get('items/generate-code', [ItemController::class, 'generateProductCode']);

// Transactions API
Route::apiResource('transactions', TransactionController::class);