# ACL Cache

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

HOWEVER, if you manipulate permission/role data directly in the database instead of calling the supplied methods, then
you will not see the changes reflected in the application unless you manually reset the cache.

## Manual Cache Reset

To manually reset the cache for this package, run:
```bash
php artisan acl:cache:clear
```

> It is equivalent to running `php artisan cache:forget konekt.acl.cache`

## Cache Identifier

TIP: If you are leveraging a caching service such as `redis` or `memcached` and there are other sites
running on your server, you could run into cache clashes. It is prudent to set your own cache `prefix`
in `/config/cache.php` to something unique for each application. This will prevent other applications
from accidentally using/changing your cached data.

---

**Next**: [Extras &raquo;](extras.md)
