<?php

namespace Application\Admin\Products\Controllers;

use Application\Admin\Products\Requests\CreateRequest;
use Application\Admin\Products\Requests\DeleteRequest;
use Application\Admin\Products\Requests\UpdateRequest;
use Domain\Products\Actions\CreateProductAction;
use Domain\Products\Actions\DeleteProductAction;
use Application\Admin\Products\Requests\ReadRequest;
use Domain\Products\Actions\UpdateProductAction;
use Domain\Products\DTO\ProductData;
use Domain\Products\Models\Product;
use Illuminate\Http\Response;
use Support\BaseController;

class ProductController extends BaseController
{
    public function index(ReadRequest $request)
    {
        return Product::paginate(10);
    }

    public function store(CreateRequest $request, CreateProductAction $action, ProductData $data): Response
    {
        $product = $action->run($data);

        if ($product) {
            return response(__("Product - {$product->id} успешно добавлен!"));
        }

        return response(__("Во время создания продукта что-то пошло не так!"), 403);
    }

    public function update(UpdateRequest $request, UpdateProductAction $action, ProductData $data): Response
    {
        $updated = $action->run($data);

        if ($updated) {
            return response(__("Продукт обновлен!"));
        }

        return response(__("Что-то пошло не так"));
    }

    public function delete(DeleteRequest $request, DeleteProductAction $action, ProductData $data)
    {
        $deleted = $action->run($request->id);

        if ($deleted) {
            return response(__("Продукт удален!"));
        }

        return response(__("Что-то пошло не так"));
    }
}