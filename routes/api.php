<?php

use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Product routes
    Route::controller(ProductController::class)->group(function () {
        Route::get('/products', 'index');
        Route::post('/products', 'store');
        Route::get('/products/{id}', 'show');
        Route::put('/products/{id}', 'update');
        Route::delete('/products/{id}', 'destroy');
    });

    // Inventory routes
    Route::controller(InventoryController::class)->group(function () {
        Route::post('/inventory', 'store');
        Route::get('/inventory', 'index');
    });

    // Sales routes
    Route::controller(SaleController::class)->group(function () {
        Route::post('/sales', 'store');
        Route::get('/sales/{id}', 'show');
    });
});
