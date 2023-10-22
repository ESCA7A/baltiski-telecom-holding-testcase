<?php

namespace App\Console\Commands\Permissions;

use Domain\Permissions\Actions\CreatePermissionsAction;
use Domain\Permissions\Actions\CreateRolesAction;
use Domain\Permissions\DTO\PermissionData;
use Domain\Permissions\DTO\RoleData;
use Illuminate\Console\Command;

class BuildRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build:roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создание ролей';

    /**
     * Execute the console command.
     */
    public function handle(CreatePermissionsAction $createPermissionsAction, CreateRolesAction $createRolesAction)
    {
        $roles = RoleData::fromConfig();
        $rolesCollection = $createRolesAction->run($roles);

        $permissions = PermissionData::fromConfig();
        $permissionsCollect = $createPermissionsAction->run($permissions);
    }
}
