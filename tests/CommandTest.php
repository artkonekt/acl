<?php

namespace Konekt\Acl\Test;

use Artisan;
use Konekt\Acl\Models\Role;
use Konekt\Acl\Models\Permission;
use Konekt\Acl\Test\Concerns\InteractsWithAclCache;

class CommandTest extends TestCase
{
    use InteractsWithAclCache;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpCacheTest();
    }

    /** @test */
    public function it_can_create_a_role()
    {
        Artisan::call('acl:create-role', ['name' => 'new-role']);

        $this->assertCount(1, Role::where('name', 'new-role')->get());
    }

    /** @test */
    public function it_can_create_a_role_with_a_specific_guard()
    {
        Artisan::call('acl:create-role', [
            'name'  => 'new-role',
            'guard' => 'api',
        ]);

        $this->assertCount(1, Role::where('name', 'new-role')
            ->where('guard_name', 'api')
            ->get());
    }

    /** @test */
    public function it_can_create_a_permission()
    {
        Artisan::call('acl:create-permission', ['name' => 'new-permission']);

        $this->assertCount(1, Permission::where('name', 'new-permission')->get());
    }

    /** @test */
    public function it_can_create_a_permission_with_a_specific_guard()
    {
        Artisan::call('acl:create-permission', [
            'name'  => 'new-permission',
            'guard' => 'api',
        ]);

        $this->assertCount(1, Permission::where('name', 'new-permission')
            ->where('guard_name', 'api')
            ->get());
    }

    /** @test */
    public function the_cache_clear_command_flushes_the_permission_cache()
    {
        $this->testUserRole->givePermissionTo($this->testUserPermission);

        $this->resetQueryCount();
        $this->registrar->getPermissions();
        $this->assertQueryCount($this->queriesPerCacheProvision);

        $this->resetQueryCount();
        $this->registrar->getPermissions();
        $this->assertQueryCount(0);

        Artisan::call('acl:cache:clear');
        $this->resetQueryCount();
        $this->registrar->getPermissions();
        $this->assertQueryCount($this->queriesPerCacheProvision);
    }
}
