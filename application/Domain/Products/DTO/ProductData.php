<?php

namespace Domain\Products\DTO;

use Domain\Products\Enums\Status;
use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class ProductData extends Data
{
    public ?string $name;
    public string $article;
    public Status $status;
    public ?string $data;

    public static function fromRequest(Request $request): ProductData
    {
        return new self([
            'name' => $request->name,
            'article' => $request->article,
            'status' => $request->status,
            'data' => $request->data,
        ]);
    }
}