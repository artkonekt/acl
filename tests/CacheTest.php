<?php

namespace Konekt\Acl\Test;

use Illuminate\Support\Facades\DB;
use Konekt\Acl\Models\PermissionProxy;
use Konekt\Acl\Models\RoleProxy;
use Konekt\Acl\PermissionRegistrar;

class CacheTest extends TestCase
{
    const QUERIES_PER_CACHE_PROVISION = 2;

    protected $registrar;

    public function setUp()
    {
        parent::setUp();

        $this->registrar = app(PermissionRegistrar::class);

        $this->registrar->forgetCachedPermissions();

        DB::connection()->enableQueryLog();
    }

    /** @test */
    public function it_can_cache_the_permissions()
    {
        $this->registrar->getPermissions();

        $this->assertQueryCount(self::QUERIES_PER_CACHE_PROVISION);

        $this->registrar->getPermissions();

        $this->assertQueryCount(self::QUERIES_PER_CACHE_PROVISION);
    }

    /** @test */
    public function it_flushes_the_cache_when_creating_a_permission()
    {
        PermissionProxy::create(['name' => 'new']);

        $this->resetQueryCount();

        $this->registrar->getPermissions();

        $this->assertQueryCount(self::QUERIES_PER_CACHE_PROVISION);
    }

    /** @test */
    public function it_flushes_the_cache_when_creating_a_role()
    {
        $role = RoleProxy::create(['name' => 'new']);

        $this->resetQueryCount();

        $this->registrar->getPermissions();

        $this->assertQueryCount(self::QUERIES_PER_CACHE_PROVISION);
    }

    /** @test */
    public function it_flushes_the_cache_when_updating_a_role()
    {
        $role = RoleProxy::create(['name' => 'new']);

        $role->name = 'other name';
        $role->save();

        $this->resetQueryCount();

        $this->registrar->getPermissions();

        $this->assertQueryCount(self::QUERIES_PER_CACHE_PROVISION);
    }

    /** @test */
    public function user_creation_should_not_flush_the_cache()
    {
        $this->registrar->getPermissions();

        User::create(['email' => 'new']);

        $this->resetQueryCount();

        $this->registrar->getPermissions();

        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_flushes_the_cache_when_giving_a_permission_to_a_role()
    {
        $this->testUserRole->givePermissionTo($this->testUserPermission);

        $this->resetQueryCount();

        $this->registrar->getPermissions();

        $this->assertQueryCount(self::QUERIES_PER_CACHE_PROVISION);
    }

    /** @test */
    public function has_permission_to_should_use_the_cache()
    {
        $this->testUserRole->givePermissionTo(['edit-articles', 'edit-news']);
        $this->testUser->assignRole('testRole');

        $this->resetQueryCount();

        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));

        $this->assertQueryCount(self::QUERIES_PER_CACHE_PROVISION + 2); // + 2 for getting the User's relations
        $this->resetQueryCount();

        $this->assertTrue($this->testUser->hasPermissionTo('edit-news'));

        $this->assertQueryCount(0);

        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));

        $this->assertQueryCount(0);
    }

    protected function assertQueryCount(int $expected)
    {
        $this->assertCount($expected, DB::getQueryLog());
    }

    protected function resetQueryCount()
    {
        DB::flushQueryLog();
    }
}
