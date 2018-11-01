<?php

namespace Konekt\Acl\Providers;

use Konekt\Acl\Commands\ClearCache;
use Konekt\Acl\Commands\CreatePermission;
use Konekt\Acl\Commands\CreateRole;
use Konekt\Acl\Models\Permission;
use Konekt\Acl\Models\Role;
use Konekt\Acl\PermissionRegistrar;
use Konekt\Concord\BaseModuleServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Permission::class,
        Role::class
    ];

    protected $permissionLoader;

    public function boot()
    {
        parent::boot();

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateRole::class,
                CreatePermission::class,
                ClearCache::class
            ]);
        }

        $this->permissionLoader = $this->app->make(PermissionRegistrar::class);

        $this->permissionLoader->registerPermissions();
    }

    public function register()
    {
        parent::register();

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
