# Upgrade

## From v1 to v2

### Interface Changes

The `getName()` method has been added to the `Permission` and `Role` interfaces.
In case your application uses custom models that aren't derived from the base models from this package,
then you have to implement these methods in those classes.

### Finder Methods Now Return Null

The `Permission::findByName()`, `Role::findByName()` and the `Role::findById()` methods no longer throw an exception
when no result was not found, but return `NULL` instead. If your code relies on catching those exceptions and/or assuming
that the return value is always an object, then the logic needs to be adjusted according to the new behavior.

### No More Collections

The `givePermissionTo()` method (from the `HasPermissions` trait) no longer accepts an array or a Collection parameter,
only a variadic `string|Permission` parameters.

Eg.:

```php
// Used to work in ACL v1.x, no longer works in v2.x:
$user->givePermissionTo(['edit users', 'create users']);
$user->givePermissionTo(Permission::where('name', 'like', '% invoices')->get());

//In v2.x use this instead:
$user->givePermissionTo('edit users', 'create users');
$user->givePermissionTo(...Permission::where('name', 'like', '% invoices')->get()->all());
```

### Renamed Methods and Scopes

- The `HasRoles::permission()` query scope has been renamed to `havingPermissions()`
- The renamed `havingPermissions()` scope no longer accepts and array or Collection, only a variadic `string|Permission` parameter list
- The `HasRoles::role()` query scope has been renamed to `havingRoles()`

---

**Next**: [Usage &raquo;](usage.md)
