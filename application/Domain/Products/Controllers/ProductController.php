<?php

namespace Domain\Products\Controllers;

use Domain\Products\Model\Product;
use Domain\Products\Actions\CreateProductAction;
use Domain\Products\DTO\ProductData;
use Domain\Products\Requests\CreateRequest;
use Support\BaseController;

class ProductController extends BaseController
{
    public function index()
    {
        return Product::all();
    }

    public function store(CreateRequest $request, CreateProductAction $action)
    {
        $product = $action->run(ProductData::fromRequest($request));

        if ($product) {
            return response(__("Продукт - {$product->id} успешно создан!"))->json($product);
        }

        return response(__("Во время создания продукта что-то пошло не так!"), 403);
    }

//    public function update(UpdateRequest $request, UpdateProductAction $action)
//    {
//    }
}