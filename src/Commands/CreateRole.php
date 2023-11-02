<?php

declare(strict_types=1);

namespace Konekt\Acl\Commands;

use Illuminate\Console\Command;
use Konekt\Acl\Models\RoleProxy;

class CreateRole extends Command
{
    protected $signature = 'acl:create-role
        {name : The name of the role}
        {guard? : The name of the guard}';

    protected $description = 'Create a role';

    public function handle()
    {
        $role = RoleProxy::create([
            'name' => $this->argument('name'),
            'guard_name' => $this->argument('guard'),
        ]);

        $this->info("Role `{$role->name}` created");
    }
}
