<?php

namespace Domain\Products\DTO;

use Domain\Products\Enums\Status;
use Spatie\LaravelData\Data;

class ProductData extends Data
{
    public ?string $name;

    public string $article;

    public Status $status;

    public $data;
}
