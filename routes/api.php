<?php

use App\Http\Controllers\API\TransactionChargeController;
use App\Http\Controllers\API\TransactionTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::prefix('v1')->group(function () {
    // Transaction Types
    Route::get('/transaction-types', [TransactionTypeController::class, 'index']);
    Route::post('/transaction-types', [TransactionTypeController::class, 'store']);

    // Charge Calculation
    Route::post('/calculate-charges', [TransactionChargeController::class, 'calculateCharge']);
});
