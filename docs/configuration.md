# Configuration

You can modify the configuration within `app/concord.php`:

```php
return [
    'modules' => [
        // ...
        Konekt\Acl\Providers\ModuleServiceProvider::class => [
            
            /*
             * By default all permissions will be cached for 24 hours unless a permission or
             * role is updated. Then the cache will be flushed immediately.
             */             
            'cache_expiration_time' => 60 * 24,
        
            /*
             * When set to true, the required permission/role names are added to the exception
             * message. This could be considered an information leak in some contexts, so
             * the default setting is false here for optimum safety.
             */        
            'display_permission_in_exception' => false
        ]
    ]
];
```

### Custom Models

The main difference between this fork and the original Spatie version is that models are managed via Concord's
[model proxies feature](https://konekt.dev/concord/1.x/proxies).

Therefore, model classes and table names have been removed from config and have been added as Concord models.
To use your own custom models, use Concord's facilities:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Konekt\Acl\Contracts\Role as RoleContract;
use Konekt\Acl\Contracts\Permission as PermissionContract;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // To override the role model
        $this->app->concord->registerModel(RoleContract::class, \App\Role::class);
        // To override the Permission model
        $this->app->concord->registerModel(PermissionContract::class, \App\Permission::class);
    }
}
```

If you also want to change the names of the db tables, set the `$table` property of the model:

```php
namespace App;

use Konekt\Acl\Models\Role as BaseRole;

class Role extends BaseRole
{
    protected $table = 'my_roles_table_name';
}
```

> **Important**: Make sure any custom model class implements its
> appropriate interface: `Konekt\Acl\Contracts\Role` and `Konekt\Acl\Contracts\Permission`, respectively

---

**Next**: [Usage &raquo;](usage.md)
