<?php

namespace Konekt\Acl\Providers;

use Konekt\Acl\Models\Permission;
use Konekt\Acl\Models\Role;
use Konekt\Concord\BaseModuleServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Konekt\Acl\Contracts\Role as RoleContract;
use Konekt\Acl\Contracts\Permission as PermissionContract;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Permission::class,
        Role::class
    ];

    public function boot(PermissionRegistrar $permissionLoader)
    {
        $this->publishes([
            __DIR__.'/../config/permission.php' => $this->app->configPath().'/permission.php',
        ], 'config');

        if (! class_exists('CreatePermissionTables')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../database/migrations/create_permission_tables.php.stub' => $this->app->databasePath()."/migrations/{$timestamp}_create_permission_tables.php",
            ], 'migrations');
        }

        $permissionLoader->registerPermissions();
    }

    public function register()
    {
        parent::register();

        $this->mergeConfigFrom(
            __DIR__.'/../config/permission.php',
            'permission'
        );

        $this->registerBladeExtensions();
    }

    protected function registerBladeExtensions()
    {
        $this->app->afterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            $bladeCompiler->directive('role', function ($arguments) {
                list($role, $guard) = explode(',', $arguments.',');

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('endrole', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasrole', function ($arguments) {
                list($role, $guard) = explode(',', $arguments.',');

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('endhasrole', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasanyrole', function ($arguments) {
                list($roles, $guard) = explode(',', $arguments.',');

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasAnyRole({$roles})): ?>";
            });
            $bladeCompiler->directive('endhasanyrole', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasallroles', function ($arguments) {
                list($roles, $guard) = explode(',', $arguments.',');

                return "<?php if(auth({$guard})->check() && auth({$guard})->user()->hasAllRoles({$roles})): ?>";
            });
            $bladeCompiler->directive('endhasallroles', function () {
                return '<?php endif; ?>';
            });
        });
    }
}
