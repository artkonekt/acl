<?php

namespace Konekt\Acl\Test;

use Konekt\Acl\Models\PermissionProxy;
use Konekt\Acl\Models\RoleProxy;
use Konekt\Acl\PermissionRegistrar;
use Konekt\Acl\Providers\ModuleServiceProvider;
use Konekt\Concord\ConcordServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /** @var \Konekt\Acl\Test\User */
    protected $testUser;

    /** @var \Konekt\Acl\Test\Admin */
    protected $testAdmin;

    /** @var \Konekt\Acl\Models\Role */
    protected $testUserRole;

    /** @var \Konekt\Acl\Models\Role */
    protected $testAdminRole;

    /** @var \Konekt\Acl\Models\Permission */
    protected $testUserPermission;

    /** @var \Konekt\Acl\Models\Permission */
    protected $testAdminPermission;

    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        $this->testUser           = User::first();
        $this->testUserRole       = RoleProxy::find(1);
        $this->testUserPermission = PermissionProxy::find(1);

        $this->testAdmin           = Admin::first();
        $this->testAdminRole       = RoleProxy::find(3);
        $this->testAdminPermission = PermissionProxy::find(3);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ConcordServiceProvider::class
        ];
    }

    /**
     * Set up the environment.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('view.paths', [__DIR__.'/resources/views']);

        // Set-up admin guard
        $app['config']->set('auth.guards.admin', ['driver' => 'session', 'provider' => 'admins']);
        $app['config']->set('auth.providers.admins', ['driver' => 'eloquent', 'model' => Admin::class]);

        // Use test User model for users provider
        $app['config']->set('auth.providers.users.model', User::class);
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
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

        include_once __DIR__.'/../src/resources/database/migrations/2017_05_31_113121_create_acl_tables.php';

        (new \CreateAclTables())->up();

        User::create(['email' => 'test@user.com']);
        Admin::create(['email' => 'admin@user.com']);
        RoleProxy::create(['name' => 'testRole']);
        RoleProxy::create(['name' => 'testRole2']);
        RoleProxy::create(['name' => 'testAdminRole', 'guard_name' => 'admin']);
        PermissionProxy::create(['name' => 'edit-articles']);
        PermissionProxy::create(['name' => 'edit-news']);
        PermissionProxy::create(['name' => 'admin-permission', 'guard_name' => 'admin']);
    }

    /**
     * Reload the permissions.
     */
    protected function reloadPermissions()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Refresh the testUser.
     */
    public function refreshTestUser()
    {
        $this->testUser = $this->testUser->fresh();
    }

    /**
     * Refresh the testAdmin.
     */
    public function refreshTestAdmin()
    {
        $this->testAdmin = $this->testAdmin->fresh();
    }

    /**
     * Refresh the testUserPermission.
     */
    public function refreshTestUserPermission()
    {
        $this->testUserPermission = $this->testUserPermission->fresh();
    }

    /**
     * @inheritdoc
     */
    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);
        $app['config']->set('concord.modules', [
            ModuleServiceProvider::class
        ]);
    }
}
