<?php

namespace Domain\Products\Actions;

use Domain\Products\Model\Product;
use Domain\Products\DTO\ProductData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateProductAction
{
    public function run(ProductData $data): ?Product
    {
        $product = null;

        try {
            return DB::transaction(function () use ($data) {
                $product = (new Product())->create([
                    'name' => $data->name,
                    'article' => $data->article,
                    'status' => $data->status,
                    'data' => $data->data,
                ]);

                return $product;
            });
        } catch (Throwable $e) {
            Log::debug(__("Во время создания продукта что-то пошло не так"));
        }

        return $product;
    }
}