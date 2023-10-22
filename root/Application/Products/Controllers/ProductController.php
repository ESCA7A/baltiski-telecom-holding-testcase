<?php

namespace Application\Products\Controllers;

use Domain\Products\Models\Product;
use Domain\Products\Requests\ReadRequest;
use Support\BaseController;

class ProductController extends BaseController
{
    public function index(ReadRequest $request)
    {
        return Product::paginate(15);
    }
}
