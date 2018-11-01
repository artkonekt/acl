# Concord (Laravel) Module For Handling Permissions And Roles

> This package is a fork of [Spatie's Permission v2.1.5](https://github.com/spatie/laravel-permission).
> Reason for fork was to convert the package into a [Concord compliant](https://github.com/artkonekt/concord) module.
>
> **v1.0**: Most of the changes have been ported from Spatie v2.9.0

[![Travis](https://img.shields.io/travis/artkonekt/acl.svg?style=flat-square)](https://travis-ci.org/artkonekt/acl)
[![Packagist version](https://img.shields.io/packagist/v/konekt/acl.svg?style=flat-square)](https://packagist.org/packages/konekt/acl)
[![Packagist downloads](https://img.shields.io/packagist/dt/konekt/acl.svg?style=flat-square)](https://packagist.org/packages/konekt/acl)
[![StyleCI](https://styleci.io/repos/93518548/shield?branch=master)](https://styleci.io/repos/93518548)
[![MIT Software License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.md)

This package allows you to manage user permissions and roles in a database.

Once installed you can do stuff like this:

```php
// Adding permissions to a user
$user->givePermissionTo('edit articles');

// Adding permissions via a role
$user->assignRole('writer');

$role->givePermissionTo('edit articles');
```

If you're using multiple guards we've got you covered as well. Every guard will have its own set of permissions and roles that can be assigned to the guard's users. Read about it in the [using multiple guards](#using-multiple-guards) section of the readme.

Because all permissions will be registered on [Laravel's gate](https://laravel.com/docs/5.5/authorization), you can test if a user has a permission with Laravel's default `can` function:

```php
$user->can('edit articles');
```

The original author of this package is Spatie, a webdesign agency in Antwerp, Belgium. You'll find an overview of all
their open source projects [on their website](https://spatie.be/opensource).

## Installation

This version of the package is intended ton be used as a [Concord module](https://github.com/artkonekt/concord/blob/master/docs/modules.md) in Laravel 5.4 or higher. If you don't use Concord take a look at [the original Spatie package](https://github.com/spatie/laravel-permission).

You can install the package via composer:

``` bash
composer require konekt/acl
```

Now add the module in `config/concord.php` file:

```php
'modules' => [
    // ...
    Konekt\Acl\Providers\ModuleServiceProvider::class,
];
```

The package contains some migrations that you can run as usual by:

```bash
php artisan migrate
```

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

The main difference between this fork and the original Spatie version is
that models are managed via Concord's
[model proxies feature](https://artkonekt.github.io/concord/#/proxies).

Thus Model classes and table names have been removed from config and have
been added as Concord models. Thus you can easily replace them in your
using Concord's facilities:

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

If you also want to change the names of the db tables, set the `$table`
property of the model:

```php
namespace App;

use Konekt\Acl\Models\Role as BaseRole;

class Role extends BaseRole
{
    protected $table = 'my_roles_table_name';
}
```

> *Important*: Make sure any custom model class implements its
> appropriate interface (`Konekt\Acl\Contracts\Role`,
> `Konekt\Acl\Contracts\Permission`)

## Usage

First, add the `Konekt\Acl\Traits\HasRoles` trait to your User model(s):

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Konekt\Acl\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    
    // ...
}
```

> - note that if you need to use `HasRoles` trait with another model ex.`Page` you will also need to add `protected $guard_name = 'web';` as well to that model or you would get an error
>
>```php
>use Illuminate\Database\Eloquent\Model;
>use Spatie\Permission\Traits\HasRoles;
>
>class Page extends Model
>{
>    use HasRoles;
>
>    protected $guard_name = 'web'; // or whatever guard you want to use
>
>    // ...
>}
>```

This package allows for users to be associated with permissions and roles. Every role is associated with multiple permissions.
A `Role` and a `Permission` are regular Eloquent models. They require a `name` and can be created like this:

```php
use Konekt\Acl\Models\Role;
use Konekt\Acl\Models\Permission;

$role = Role::create(['name' => 'writer']);
$permission = Permission::create(['name' => 'edit articles']);
```

If you want to use your own models instead of the built in ones, use
their proxy counterparts instead:

```php
use Konekt\Acl\Models\RoleProxy;
use Konekt\Acl\Models\PermissionProxy;

$role = RoleProxy::create(['name' => 'writer']);
$permission = PermissionProxy::create(['name' => 'edit articles']);
```

If you're using multiple guards the `guard_name` attribute needs to be set as well. Read about it in the [using multiple guards](#using-multiple-guards) section of the readme.

The `HasRoles` trait adds Eloquent relationships to your models, which can be accessed directly or used as a base query:

```php
// get a list of all permissions directly assigned to the user
$permissions = $user->permissions;

// get all permissions inherited by the user via roles
$permissions = $user->getAllPermissions();

// get a collection of all defined roles
$roles = $user->getRoleNames(); // Returns a collection
```

The `HasRoles` trait also adds a `role` scope to your models to scope the query to certain roles or permissions:

```php
$users = User::role('writer')->get(); // Returns only users with the role 'writer'
```

The `role` scope can accept a string, a `\Spatie\Permission\Models\Role` object or an `\Illuminate\Support\Collection` object.

The same trait also adds a scope to only get users that have a certain permission.

```php
$users = User::permission('edit articles')->get(); // Returns only users with the permission 'edit articles' (inherited or directly)
```

The scope can accept a string, a `\Spatie\Permission\Models\Permission` object or an `\Illuminate\Support\Collection` object.

### Using "direct" permissions (see below to use both roles and permissions)

A permission can be given to any user:

```php
$user->givePermissionTo('edit articles');

// You can also give multiple permission at once
$user->givePermissionTo('edit articles', 'delete articles');

// You may also pass an array
$user->givePermissionTo(['edit articles', 'delete articles']);
```

A permission can be revoked from a user:

```php
$user->revokePermissionTo('edit articles');
```

Or revoke & add new permissions in one go:

```php
$user->syncPermissions(['edit articles', 'delete articles']);
```

You can test if a user has a permission:

```php
$user->hasPermissionTo('edit articles');
```

...or if a user has multiple permissions:

```php
$user->hasAnyPermission(['edit articles', 'publish articles', 'unpublish articles']);
```

Saved permissions will be registered with the `Illuminate\Auth\Access\Gate` class for the default guard. So you can
test if a user has a permission with Laravel's default `can` function:

```php
$user->can('edit articles');
```

### Using permissions via roles

A role can be assigned to any user:

```php
$user->assignRole('writer');

// You can also assign multiple roles at once
$user->assignRole('writer', 'admin');
// or as an array
$user->assignRole(['writer', 'admin']);
```

A role can be removed from a user:

```php
$user->removeRole('writer');
```

Roles can also be synced:

```php
// All current roles will be removed from the user and replaced by the array given
$user->syncRoles(['writer', 'admin']);
```

You can determine if a user has a certain role:

```php
$user->hasRole('writer');
```

You can also determine if a user has any of a given list of roles:

```php
$user->hasAnyRole(RoleProxy::all());
```

You can also determine if a user has all of a given list of roles:

```php
$user->hasAllRoles(Role::all());
```

The `assignRole`, `hasRole`, `hasAnyRole`, `hasAllRoles`  and `removeRole` functions can accept a
 string, a `\Konekt\Acl\Models\Role` object or an `\Illuminate\Support\Collection` object.

A permission can be given to a role:

```php
$role->givePermissionTo('edit articles');
```

You can determine if a role has a certain permission:

```php
$role->hasPermissionTo('edit articles');
```

A permission can be revoked from a role:

```php
$role->revokePermissionTo('edit articles');
```

The `givePermissionTo` and `revokePermissionTo` functions can accept a 
string or a `Konekt\Acl\Models\Permission` object.

Permissions are inherited from roles automatically.
Additionally, individual permissions can be assigned to the user too.
For instance:

```php
$role = Role::findByName('writer');
$role->givePermissionTo('edit articles');

$user->assignRole('writer');

$user->givePermissionTo('delete articles');
```

In the above example, a role is given permission to edit articles and this role is assigned to a user.
Now the user can edit articles and additionally delete articles. The permission of 'delete articles' is the user's direct permission because it is assigned directly to them.
When we call `$user->hasDirectPermission('delete articles')` it returns `true`,
but `false` for `$user->hasDirectPermission('edit articles')`.

This method is useful if one builds a form for setting permissions for roles and users in an application and wants to restrict or change inherited permissions of roles of the user, i.e. allowing to change only direct permissions of the user.

You can list all of these permissions:

```php
// Direct permissions
$user->getDirectPermissions(); // Or $user->permissions;

// Permissions inherited from the user's roles
$user->getPermissionsViaRoles();

// All permissions which apply on the user (inherited and direct)
$user->getAllPermissions();
```

All these responses are collections of `Konekt\Acl\Models\Permission` objects.

If we follow the previous example, the first response will be a collection with the 'delete article' permission, the 
second will be a collection with the 'edit article' permission and the third will contain both.

If we follow the previous example, the first response will be a collection with the `delete article` permission and
the second will be a collection with the `edit article` permission and the third will contain both.

### Using Blade directives
This package also adds Blade directives to verify whether the currently logged in user has all or any of a given list of roles.

Optionally you can pass in the `guard` that the check will be performed on as a second argument.

#### Blade and Roles
Test for a specific role:
```blade
@role('writer')
    I am a writer!
@else
    I am not a writer...
@endrole
```
is the same as
```blade
@hasrole('writer')
    I am a writer!
@else
    I am not a writer...
@endhasrole
```

Test for any role in a list:
```blade
@hasanyrole($collectionOfRoles)
    I have one or more of these roles!
@else
    I have none of these roles...
@endhasanyrole
<!-- or -->
@hasanyrole('writer|admin')
    I am either a writer or an admin or both!
@else
    I have none of these roles...
@endhasanyrole
```
Test for all roles:

```blade
@hasallroles($collectionOfRoles)
    I have all of these roles!
@else
    I do not have all of these roles...
@endhasallroles
// or
@hasallroles('writer|admin')
    I am both a writer and an admin!
@else
    I do not have all of these roles...
@endhasallroles
```

#### Blade and Permissions
This package doesn't add any permission-specific Blade directives. Instead, use Laravel's native `@can` directive to check if a user has a certain permission.

```blade
@can('edit articles')
  //
@endcan
```
or
```blade
@if(auth()->user()->can('edit articles') && $some_other_condition)
  //
@endif
```

## Using multiple guards

When using the default Laravel auth configuration all of the above methods will work out of the box, no extra configuration required.

However, when using multiple guards they will act like namespaces for your permissions and roles. Meaning every guard has its own set of permissions and roles that can be assigned to their user model.

### Using permissions and roles with multiple guards

By default the default guard (`config('auth.defaults.guard')`) will be used as the guard for new permissions and roles. When creating permissions and roles for specific guards you'll have to specify their `guard_name` on the model:

```php
// Create a superadmin role for the admin users
$role = RoleProxy::create(['guard_name' => 'admin', 'name' => 'superadmin']);

// Define a `publish articles` permission for the admin users belonging to the admin guard
$permission = Permission::create(['guard_name' => 'admin', 'name' => 'publish articles']);

// Define a *different* `publish articles` permission for the regular users belonging to the web guard
$permission = Permission::create(['guard_name' => 'web', 'name' => 'publish articles']);
```

To check if a user has permission for a specific guard:

```php
$user->hasPermissionTo('publish articles', 'admin');
```

### Assigning permissions and roles to guard users

You can use the same methods to assign permissions and roles to users as described above in [using permissions via roles](#using-permissions-via-roles). Just make sure the `guard_name` on the permission or role matches the guard of the user, otherwise a `GuardDoesNotMatch` exception will be thrown.

### Using blade directives with multiple guards

You can use all of the blade directives listed in [using blade directives](#using-blade-directives) by passing in the guard you wish to use as the second argument to the directive:

```blade
@role('super-admin', 'admin')
    I am a super-admin!
@else
    I am not a super-admin...
@endrole
```

## Using a middleware

This package comes with `RoleMiddleware` and `PermissionMiddleware` middleware. You can add them inside your `app/Http/Kernel.php` file.

```php
protected $routeMiddleware = [
    // ...
    'role' => \Konekt\Acl\Http\Middleware\RoleMiddleware::class,
    'permission' => \Konekt\Acl\Http\Middleware\PermissionMiddleware::class,
];
```

Then you can protect your routes using middleware rules:

```php
Route::group(['middleware' => ['role:super-admin']], function () {
    //
});

Route::group(['middleware' => ['permission:publish articles']], function () {
    //
});

Route::group(['middleware' => ['role:super-admin','permission:publish articles']], function () {
    //
});
```

Alternatively, you can separate multiple roles or permission with a `|` (pipe) character:

```php
Route::group(['middleware' => ['role:super-admin|writer']], function () {
    //
});

Route::group(['middleware' => ['permission:publish articles|edit articles']], function () {
    //
});
```

You can protect your controllers similarly, by setting desired middleware in the constructor:

```php
public function __construct()
{
    $this->middleware(['role:super-admin','permission:publish articles|edit articles']);
}
```

### Catching role and permission failures

If you want to override the default `403` response, you can catch the `UnauthorizedException` using
your app's exception handler:

```php
public function render($request, Exception $exception)
{
    if ($exception instanceof \Konekt\Acl\Exceptions\UnauthorizedException) {
        // Code here ...
    }

    return parent::render($request, $exception);
}
```


## Using artisan commands

You can create a role or permission from a console with artisan commands.

```bash
php artisan acl:create-role writer
```

```bash
php artisan acl:create-permission 'edit articles'
```

When creating permissions and roles for specific guards you can specify the guard names as a second argument:

```bash
php artisan acl:create-role writer web
```

```bash
php artisan acl:create-permission 'edit articles' web
```

## Unit Testing

In your application's tests, if you are not seeding roles and permissions as part of your test `setUp()` then you may run into a chicken/egg situation where roles and permissions aren't registered with the gate (because your tests create them after that gate registration is done). Working around this is simple: In your tests simply add a `setUp()` instruction to re-register the permissions, like this:

```php
    public function setUp()
    {
        // first include all the normal setUp operations
        parent::setUp();

        // now re-register all the roles and permissions
        $this->app->make(\Konekt\Acl\PermissionRegistrar::class)->registerPermissions();
    }
```

## Database Seeding

Two notes about Database Seeding:

1. It is best to flush the `konekt.acl.cache` before seeding, to avoid cache conflict errors. This can be done from an Artisan command (see Troubleshooting: Cache section, later) or directly in a seeder class (see example below).

2. Here's a sample seeder, which clears the cache, creates permissions and then assigns permissions to roles:

	```php
	use Illuminate\Database\Seeder;
	use Konekt\Acl\Models\Role;
	use Konekt\Acl\Models\Permission;

	class RolesAndPermissionsSeeder extends Seeder
	{
	    public function run()
    	{
        	// Reset cached roles and permissions
	        app()['cache']->forget('konekt.acl.cache');

	        // create permissions
	        Permission::create(['name' => 'edit articles']);
	        Permission::create(['name' => 'delete articles']);
	        Permission::create(['name' => 'publish articles']);
	        Permission::create(['name' => 'unpublish articles']);

	        // create roles and assign existing permissions
	        $role = Role::create(['name' => 'writer']);
	        $role->givePermissionTo('edit articles');
	        $role->givePermissionTo('delete articles');

	        $role = Role::create(['name' => 'admin']);
	        $role->givePermissionTo('publish articles');
	        $role->givePermissionTo('unpublish articles');
	    }
	}

	```

## Extending

If you need to extend or replace the existing `Role` or `Permission` models you just need to 
keep the following things in mind:

- Your `Role` model needs to implement the `Konekt\Acl\Contracts\Role` contract
- Your `Permission` model needs to implement the `Konekt\Acl\Contracts\Permission` contract
- In you app service provider invoke:
    ```php
        $this->app->concord->registerModel(Konekt\Acl\Contracts\Role::class, \App\Role::class);
    ```
## Cache

Role and Permission data are cached to speed up performance.

When you use the supplied methods for manipulating roles and permissions, the cache is automatically reset for you:

```php
$user->assignRole('writer');
$user->removeRole('writer');
$user->syncRoles(params);
$role->givePermissionTo('edit articles');
$role->revokePermissionTo('edit articles');
$role->syncPermissions(params);
$permission->assignRole('writer');
$permission->removeRole('writer');
$permission->syncRoles(params);
```

HOWEVER, if you manipulate permission/role data directly in the database instead of calling the supplied methods, then you will not see the changes reflected in the application unless you manually reset the cache.

### Manual cache reset

To manually reset the cache for this package, run:
```bash
php artisan acl:cache:clear
```

> It is equivalent to running `php artisan cache:forget konekt.acl.cache`

### Cache Identifier

TIP: If you are leveraging a caching service such as `redis` or `memcached` and there are other sites
running on your server, you could run into cache clashes. It is prudent to set your own cache `prefix`
in `/config/cache.php` to something unique for each application. This will prevent other applications
from accidentally using/changing your cached data.


## Need a UI?

The Konekt [AppShell Package](https://github.com/artkonekt/appshell) is an extensible Business Application boilerplate that incorporates this Acl package and contains a UI for managing permissions, roles & users.

This package doesn't come with any screens out of the box. To get started check out [this extensive tutorial](https://scotch.io/tutorials/user-authorization-in-laravel-54-with-spatie-laravel-permission) by [Caleb Oki](http://www.caleboki.com/).


## Changelog

Please see [Changelog](Changelog.md) for more information what has changed recently.

## Testing

``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Postcardware

You're free to use this package (it's [MIT-licensed](LICENSE.md)), but if it makes it to your production environment Spatie will highly appreciate you send them a postcard from your hometown, mentioning which of their package(s) you are using.

Their address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

All received postcards are published [on spatie.be](https://spatie.be/en/opensource/postcards).


## Credits

- [Attila Fulop](https://github.com/fulopattila122)
- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

This package is a modified variant of [Spatie Permission package](https://github.com/spatie/laravel-permission) which is heavily based on [Jeffrey Way](https://twitter.com/jeffrey_way)'s awesome [Laracasts](https://laracasts.com) lessons
on [permissions and roles](https://laracasts.com/series/whats-new-in-laravel-5-1/episodes/16). His original code
can be found [in this repo on GitHub](https://github.com/laracasts/laravel-5-roles-and-permissions-demo).

Special thanks to [Alex Vanderbist](https://github.com/AlexVanderbist) who greatly helped with Spatie/Permission `v2`.

## Resources

- [How to create a UI for managing the permissions and roles](http://www.qcode.in/easy-roles-and-permissions-in-laravel-5-4/)

## Alternatives

- [spatie/laravel-permission](https://github.com/spatie/laravel-permission)
- [JosephSilber/bouncer](https://github.com/JosephSilber/bouncer)
- [Zizaco/entrust](https://github.com/Zizaco/entrust)
- [bican/roles](https://github.com/romanbican/roles)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
