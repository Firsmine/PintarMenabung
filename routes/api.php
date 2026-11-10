<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CurrencyCategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    // currency category
    Route::get('currencies', [CurrencyCategoryController::class, 'currency']);
    Route::get('categories', [CurrencyCategoryController::class, 'category']);

    // wallets
    Route::get('wallets', [WalletController::class, 'index']);
    Route::post('wallets', [WalletController::class, 'store']);
    Route::get('wallets/{walletId}', [WalletController::class, 'show']);
    Route::put('wallets/{walletId}', [WalletController::class, 'update']);
    Route::delete('wallets/{walletId}', [WalletController::class, 'destroy']);

    // transaction
    Route::get('transactions', [TransactionController::class, 'index']);
    Route::post('transactions', [TransactionController::class, 'store']);
    Route::delete('transactions/{transactionId}', [TransactionController::class, 'destroy']);

    // report
    Route::prefix('reports/summary-by-category')->group(function () {
        Route::get('expense', [ReportController::class, 'expense']);
        Route::get('income', [ReportController::class, 'income']);
    });
});
