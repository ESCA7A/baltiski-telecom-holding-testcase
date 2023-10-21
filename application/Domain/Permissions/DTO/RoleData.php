<?php

namespace Domain\Permissions\DTO;

use Spatie\LaravelData\Data;

class RoleData extends Data
{
    public string $name;

    public string $title;

    public ?PermissionData $permissions;
}