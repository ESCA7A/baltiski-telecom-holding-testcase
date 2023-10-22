<?php

namespace Domain\Products\Actions;

use Domain\Products\Models\Product;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteProductAction
{
    public function run(Product $product): bool
    {
        try {
            DB::transaction(function () use ($product) {
                return $product->delete();
            });
        } catch (Throwable $e) {
            error_log(__($e->getMessage()));
        }

        return false;
    }
}
