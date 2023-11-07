# Permissions

The `HasRoles` trait adds Eloquent relationships to your models, which can be accessed directly or used as a base query:

```php
// get a list of all permissions directly assigned to the user
$permissions = $user->permissions;

// get all permissions inherited by the user via roles
$permissions = $user->getAllPermissions();

// get a collection of all defined roles
$roles = $user->getRoleNames(); // Returns a collection
```

The same trait also adds a scope to only get users that have a certain permission(s).

```php
$users = User::havingPermission('edit articles')->get(); // Returns only users with the permission 'edit articles' (inherited or directly)

// To find users by multiple permissions:
$users = User::havingPermissions('edit pages', 'create pages')->get();
```

### Modifying Permissions

A permission can be given to any user:

```php
$user->givePermissionTo('edit articles');

// You can also give multiple permission at once
$user->givePermissionTo('edit articles', 'delete articles');
```

A permission can be revoked from a user:

```php
$user->revokePermissionTo('edit articles');
```

Or revoke & add new permissions in one go:

```php
$user->syncPermissions('edit articles', 'delete articles'); // Removes any other permissions and assigns the given ones to the user
```

You can test if a user has a permission:

```php
$user->hasPermissionTo('edit articles');
```

...or if a user has multiple permissions:

```php
$user->hasAnyPermission('edit articles', 'publish articles', 'unpublish articles');
```

Saved permissions will be registered with the `Illuminate\Auth\Access\Gate` class for the default guard. So you can
test if a user has a permission with Laravel's default `can` function:

```php
$user->can('edit articles');
```

---

**Next**: [Roles &raquo;](roles.md)
