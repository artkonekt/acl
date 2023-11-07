# Multiple Guards

When using the default Laravel auth configuration all of the above methods will work out of the box, no extra configuration required.

However, when using multiple guards they will act like namespaces for your permissions and roles.
Meaning every guard has its own set of permissions and roles that can be assigned to their user model.

## Permissions and Roles with Multiple Guards

By default, the default guard (`config('auth.defaults.guard')`) will be used as the guard for new permissions and roles.
When creating permissions and roles for specific guards you'll have to specify their `guard_name` on the model:

```php
// Create a superadmin role for the admin users
$role = Role::create(['guard_name' => 'admin', 'name' => 'superadmin']);

// Define a `publish articles` permission for the admin users belonging to the admin guard
$permission = Permission::create(['guard_name' => 'admin', 'name' => 'publish articles']);

// Define a *different* `publish articles` permission for the regular users belonging to the web guard
$permission = Permission::create(['guard_name' => 'web', 'name' => 'publish articles']);
```

To check if a user has permission for a specific guard:

```php
$user->hasPermissionTo('publish articles', 'admin');
```

## Assigning Permissions and Roles to Guard Users

You can use the same methods to assign permissions and roles to users as described above in
[Using Permissions via Roles](#using-permissions-via-roles). Just make sure the `guard_name` on the permission or role
matches the guard of the user, otherwise a `GuardDoesNotMatch` exception will be thrown.

## Blade Directives with Multiple Guards

You can use all the blade directives listed in [Blade Directives](#blade-directives) by passing in the guard you wish to
use as the second argument to the directive:

```blade
@role('super-admin', 'admin')
    I am a super-admin!
@else
    I am not a super-admin...
@endrole
```

---

**Next**: [Middleware &raquo;](middleware.md)
