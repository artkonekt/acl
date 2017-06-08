<?php

namespace Konekt\Acl\Test;

use Konekt\Acl\Models\PermissionProxy;
use Konekt\Acl\Models\RoleProxy;
use Konekt\Acl\PermissionRegistrar;
use Konekt\Acl\Providers\ModuleServiceProvider;
use Konekt\Concord\ConcordServiceProvider;
use Monolog\Handler\TestHandler;
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

        $this->reloadPermissions();

        $this->testUser = User::first();
        $this->testUserRole = RoleProxy::find(1);
        $this->testUserPermission = PermissionProxy::find(1);

        $this->testAdmin = Admin::first();
        $this->testAdminRole = RoleProxy::find(3);
        $this->testAdminPermission = PermissionProxy::find(3);

        $this->clearLogTestHandler();
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

        $app['log']->getMonolog()->pushHandler(new TestHandler());
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
        });

        $app['db']->connection()->getSchemaBuilder()->create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
        });

        include_once __DIR__.'/../src/resources/database/migrations/2017_05_31_113121_create_acl_tables.php';

        (new \CreatePermissionTables())->up();

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
     *
     * @return bool
     */
    protected function reloadPermissions()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return app(PermissionRegistrar::class)->registerPermissions();
    }

    /**
     * Refresh the testuser.
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

    protected function clearLogTestHandler()
    {
        collect($this->app['log']->getMonolog()->getHandlers())->filter(function ($handler) {
            return $handler instanceof TestHandler;
        })->first(function (TestHandler $handler) {
            $handler->clear();
        });
    }

    protected function assertNotLogged($message, $level)
    {
        $this->assertFalse($this->hasLog($message, $level), "Found `{$message}` in the logs.");
    }

    protected function assertLogged($message, $level)
    {
        $this->assertTrue($this->hasLog($message, $level), "Couldn't find `{$message}` in the logs.");
    }

    /**
     * @param $message
     * @param $level
     *
     * @return bool
     */
    protected function hasLog($message, $level)
    {
        return collect($this->app['log']->getMonolog()->getHandlers())->filter(function ($handler) use ($message, $level) {
            return $handler instanceof TestHandler
                && $handler->hasRecordThatContains($message, $level);
        })->count() > 0;
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
