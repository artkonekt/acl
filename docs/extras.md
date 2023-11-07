# Extras

## Extending

If you need to extend or replace the existing `Role` or `Permission` models you just need to
keep the following things in mind:

- Your `Role` model needs to implement the `Konekt\Acl\Contracts\Role` contract
- Your `Permission` model needs to implement the `Konekt\Acl\Contracts\Permission` contract
- In you app service provider invoke:
    ```php
        $this->app->concord->registerModel(Konekt\Acl\Contracts\Role::class, \App\Role::class);
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

## Need a UI?

The [AppShell Package](https://konekt.dev/appshell) is an extensible Business Application boilerplate that incorporates
this Acl package and contains a UI for managing permissions, roles & users.

## Credits

This package is a modified variant of [Spatie Permission package](https://github.com/spatie/laravel-permission) which is heavily based on [Jeffrey Way](https://twitter.com/jeffrey_way)'s awesome [Laracasts](https://laracasts.com) lessons
on [permissions and roles](https://laracasts.com/series/whats-new-in-laravel-5-1/episodes/16). His original code
can be found [in this repo on GitHub](https://github.com/laracasts/laravel-5-roles-and-permissions-demo).

## Alternatives

- [spatie/laravel-permission](https://github.com/spatie/laravel-permission)
- [JosephSilber/bouncer](https://github.com/JosephSilber/bouncer)

