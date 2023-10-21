<?php

namespace Domain\Permissions\DTO;

use Spatie\LaravelData\Data;

class PermissionData extends Data
{
    public ?string $name;

    public ?string $title;

    public ?string $description = null;
}