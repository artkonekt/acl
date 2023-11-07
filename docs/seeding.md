# Database Seeding

Two notes about Database Seeding:

1. It is best to flush the `konekt.acl.cache` before seeding, to avoid cache conflict errors. This can be done from an [Artisan command](artisan.md) or directly in a seeder class (see example below).
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

---

**Next**: [Caching &raquo;](caching.md)
