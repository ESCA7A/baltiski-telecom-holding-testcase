<?php

use App\Http\Middleware\Authenticate;
use Domain\Products\Controllers\ProductController;
use Domain\Products\DTO\ProductData;

Route::prefix('products')->name('products.')->middleware(Authenticate::class)->group(function () {
    Route::get('/a', function () {
        return 'Hi user';
    });
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/store', [ProductController::class, 'store'])->name('store');
});

Route::get('/demo', function () {
    $data = collect();

    for ($i = 0; $i < 10; $i++) {
        $data->push(ProductData::from([
            'name' => 'test',
            'article' => fake()->slug(),
            'status' => \Domain\Products\Enums\Status::UNAVAILABLE,
            'data' => json_encode([fake()->title, fake()->title, fake()->title]),
        ]));
    }

    return [
        'Демо версия карточки товаров' => $data,
    ];
})->name('demo');