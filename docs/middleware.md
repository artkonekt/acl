# ACL Middleware

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

## Catching Role and Permission Failures

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

---

**Next**: [Artisan Commands &raquo;](artisan.md)
