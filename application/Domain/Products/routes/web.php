<?php

use Domain\Products\Controllers\ProductController;

Route::prefix('products')->name('products.')->group(function () {
    Route::get('/a', function () {
        return 'say hi';
    });
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/store', [ProductController::class, 'store'])->name('store');
});