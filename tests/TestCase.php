<?php

declare(strict_types=1);

namespace Konekt\Acl\Test;

use Illuminate\Database\Schema\Blueprint;
use Konekt\Acl\Models\Permission;
use Konekt\Acl\Models\Role;
use Konekt\Acl\PermissionRegistrar;
use Konekt\Acl\Providers\ModuleServiceProvider;
use Konekt\Concord\ConcordServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected User $testUser;

    protected Admin $testAdmin;

    protected Role $testUserRole;

    protected Role $testAdminRole;

    protected Permission $testUserPermission;

    protected Permission $testAdminPermission;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        $this->testUser = User::first();
        $this->testUserRole = Role::find(1);
        $this->testUserPermission = Permission::find(1);

        $this->testAdmin = Admin::first();
        $this->testAdminRole = Role::find(3);
        $this->testAdminPermission = Permission::find(3);
    }

    public function refreshTestUser()
    {
        $this->testUser = $this->testUser->fresh();
    }

    public function refreshTestAdmin()
    {
        $this->testAdmin = $this->testAdmin->fresh();
    }

    public function refreshTestUserPermission()
    {
        $this->testUserPermission = $this->testUserPermission->fresh();
    }

    protected function getPackageProviders($app)
    {
        return [
            ConcordServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('view.paths', [__DIR__ . '/resources/views']);

        // Set-up admin guard
        $app['config']->set('auth.guards.admin', ['driver' => 'session', 'provider' => 'admins']);
        $app['config']->set('auth.providers.admins', ['driver' => 'eloquent', 'model' => Admin::class]);

        // Use test User model for users provider
        $app['config']->set('auth.providers.users.model', User::class);
    }

    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->softDeletes();
        });

        $app['db']->connection()->getSchemaBuilder()->create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
        });

        include_once __DIR__ . '/../src/resources/database/migrations/2017_05_31_113121_create_acl_tables.php';

        (new \CreateAclTables())->up();

        User::create(['email' => 'test@user.com']);
        Admin::create(['email' => 'admin@user.com']);
        Role::create(['name' => 'testRole']);
        Role::create(['name' => 'testRole2']);
        Role::create(['name' => 'testAdminRole', 'guard_name' => 'admin']);
        Permission::create(['name' => 'edit-articles']);
        Permission::create(['name' => 'edit-news']);
        Permission::create(['name' => 'admin-permission', 'guard_name' => 'admin']);
    }

    protected function reloadPermissions()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);
        $app['config']->set('concord.modules', [
            ModuleServiceProvider::class,
        ]);
    }
}
