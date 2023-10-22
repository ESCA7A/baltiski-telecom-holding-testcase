<?php

namespace Domain\Permissions\DTO;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class PermissionData extends Data
{
    public ?string $name;

    public ?string $title;

    public string $guard_name = 'web';

    public ?string $description = null;

    public function __construct(
        ?string $name = null,
        ?string $title = null,
        ?string $description = null,
    ) {
        $this->name = $name;
        $this->title = $title;
        $this->description = $description;
    }

    public static function fromConfig(): Collection
    {
        $perms = config('permissions');
        $collect = collect();

        foreach ($perms as $permission) {
            $collect->push(self::from($permission));
        }

        throw_if($collect->isEmpty(), __("Политики не найдены"));

        return $collect;
    }
}