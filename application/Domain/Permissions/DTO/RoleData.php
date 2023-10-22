<?php

namespace Domain\Permissions\DTO;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\Permission\Models\Role;

class RoleData extends Data
{
    public string $name;

    public string $title;

    public string $guard_name = 'web';

    /**
     * @var array $permissions
     */
    public array $permissions;

    public function __construct(
        string $name,
        string $title,
        array $permissions,
    ) {
        $this->name = $name;
        $this->title = $title;
        $this->permissions = $permissions;
    }

    public static function fromConfig(): Collection
    {
        $roles = config('roles');
        $collect = collect();

        foreach ($roles as $role) {
            $collect->push(self::from($role));
        }

        throw_if($collect->isEmpty(), __("Роли не найдены"));

        return $collect;
    }
}