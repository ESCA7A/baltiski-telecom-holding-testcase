<?php

namespace Domain\Permissions\Actions;

use Domain\Permissions\DTO\RoleData;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class CreateRolesAction
{
    public function run(Collection $roles): Collection
    {
        $collect = collect();

        /**
         * @var RoleData $role
         */
        foreach ($roles as $role) {
            $rolePerms = collect($role->permissions);

            /** @var Role $newRole */
            $newRole = Role::findOrCreate($role->name);

            $rolePerms->each(fn($perm) => (!$newRole->hasPermissionTo($perm)) ? $newRole->givePermissionTo($perm) : false);

            $collect->push($newRole);
        }

        return $collect;
    }
}