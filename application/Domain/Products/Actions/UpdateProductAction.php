<?php

namespace Domain\Products\Actions;

use Domain\Products\DTO\ProductData;
use Domain\Products\Models\Product;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateProductAction
{
    public function run(ProductData $data)
    {
        try {
            DB::transaction(function () use ($data) {
                return Product::update([
                    'name' => $data->name,
                    'article' => $data->article,
                    'status' => $data->status,
                    'data' => $data->data,
                ]);
            });
        } catch (Throwable $e) {
            error_log(__($e->getMessage()));
        }

        return false;
    }
}