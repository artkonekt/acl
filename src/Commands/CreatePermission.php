<?php

declare(strict_types=1);

namespace Konekt\Acl\Commands;

use Illuminate\Console\Command;
use Konekt\Acl\Models\PermissionProxy;

class CreatePermission extends Command
{
    protected $signature = 'acl:create-permission
                {name : The name of the permission}
                {guard? : The name of the guard}';

    protected $description = 'Create a permission';

    public function handle()
    {
        $permission = PermissionProxy::create([
            'name' => $this->argument('name'),
            'guard_name' => $this->argument('guard'),
        ]);

        $this->info("Permission `{$permission->name}` created");
    }
}
