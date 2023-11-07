# Usage

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

> ⚠ Note that if you need to use `HasRoles` trait with another model ex.`Page` you will also need to add `protected $guard_name = 'web';` as well to that model, otherwise you'll get an error
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

## Roles & Permissions

This package allows for users to be associated with permissions and roles. Every role is associated with multiple permissions.
A `Role` and a `Permission` are regular Eloquent models. They require a `name` and can be created like this:

```php
use Konekt\Acl\Models\Role;
use Konekt\Acl\Models\Permission;

$role = Role::create(['name' => 'writer']);
$permission = Permission::create(['name' => 'edit articles']);
```

If you're developing a library, and you want your users to customize the models instead of the built-in ones,
you can use their proxy counterparts that will forward the calls to the custom models:

```php
use Konekt\Acl\Models\RoleProxy;
use Konekt\Acl\Models\PermissionProxy;

$role = RoleProxy::create(['name' => 'writer']);
$permission = PermissionProxy::create(['name' => 'edit articles']);
```

If you're using multiple guards the `guard_name` attribute needs to be set as well.
Read about it in the [using multiple guards](multiple-guards.md) section of the readme.

---

**Next**: [Permissions &raquo;](permissions.md)
