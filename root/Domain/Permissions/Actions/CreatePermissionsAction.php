<?php

namespace Domain\Permissions\Actions;

use Domain\Permissions\DTO\PermissionData;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class CreatePermissionsAction
{
    public function run(Collection $permissions): Collection
    {
        $collect = collect();

        /**
         * @var PermissionData $permission
         */
        foreach ($permissions as $permission) {
            $perm = Permission::findOrCreate($permission->name);
            $collect->push($perm);
        }

        return $collect;
    }
}
