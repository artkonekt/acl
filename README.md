# Concord (Laravel) Module For Handling Permissions And Roles

[![Tests](https://img.shields.io/github/actions/workflow/status/artkonekt/acl/tests.yml?branch=master&style=flat-square)](https://github.com/artkonekt/acl/actions?query=workflow%3Atests)
[![Packagist version](https://img.shields.io/packagist/v/konekt/acl.svg?style=flat-square)](https://packagist.org/packages/konekt/acl)
[![Packagist downloads](https://img.shields.io/packagist/dt/konekt/acl.svg?style=flat-square)](https://packagist.org/packages/konekt/acl)
[![StyleCI](https://styleci.io/repos/93518548/shield?branch=master)](https://styleci.io/repos/93518548)
[![MIT Software License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.md)

This package allows you to manage user permissions and roles in a database.

## Intro

Once installed you can do stuff like this:

```php
// Adding permissions to a user
$user->givePermissionTo('edit articles');

// Adding permissions via a role
$user->assignRole('writer');

$role->givePermissionTo('edit articles');
```

Because all permissions will be registered on [Laravel's gate](https://laravel.com/docs/10.x/authorization), you can test if a user has a permission with Laravel's default `can` function:

```php
$user->can('edit articles');
```

### Package Origins

- This package is a fork of [Spatie's Permission v2.1.5](https://github.com/spatie/laravel-permission).
- Reason for the fork was to convert the package into a [Concord compliant](https://konekt.dev/concord) module.
- As of **v1.0**: most of the changes have been ported from Spatie v2.9.0
- Beginning with **v2.0**, this package no longer maintains compatibility and feature parity with the Spatie Permission package.
- The most important feature of v2 is the possibility of _Sharing Eloquent models_ across users.

## Documentation

https://konekt.dev/acl/master/README

## Changelog

Please see [Changelog](Changelog.md) for more information what has changed recently.

## Credits

This package is a modified variant of [Spatie Permission package](https://github.com/spatie/laravel-permission) which is heavily based on [Jeffrey Way](https://twitter.com/jeffrey_way)'s awesome [Laracasts](https://laracasts.com) lessons
on [permissions and roles](https://laracasts.com/series/whats-new-in-laravel-5-1/episodes/16). His original code
can be found [in this repo on GitHub](https://github.com/laracasts/laravel-5-roles-and-permissions-demo).

## Alternatives

- [spatie/laravel-permission](https://github.com/spatie/laravel-permission)
- [JosephSilber/bouncer](https://github.com/JosephSilber/bouncer)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
