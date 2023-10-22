<?php

use App\Http\Middleware\Authenticate;
use Application\Products\Admin\Controllers\ProductController;

Route::prefix('admin')->name('admin.')->middleware(Authenticate::class)->group(function () {
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/a', function () {
            return 'Hi admin!';
        });
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/store', [ProductController::class, 'store'])->name('store');
    });
});
