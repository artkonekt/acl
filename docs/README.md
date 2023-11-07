# ACL for Laravel Documentation

ACL is a Laravel package to manage user permissions and roles in a database.

## Features

- Permissions like `list invoices`, `create projects` etc.
- Roles like `Editor`, `Super Admin`, `Developer`, `Content Manager`, etc
- Integration with Laravel's authorization system:
  - PHP: `$user->can('create invoices')`
  - Blade: `@can('list expenses') ... @endcan`
  - Multiple guards are supported

## Package Origins

- This package is a fork of [Spatie's Permission v2.1.5](https://github.com/spatie/laravel-permission).
- Reason for the fork was to convert the package into a [Concord compliant](https://konekt.dev/concord) module.
- As of **v1.0**: most of the changes have been ported from Spatie v2.9.0
- Beginning with **v2.0**, this package no longer maintains compatibility and feature parity with the Spatie Permission package.
- The most important feature of v2 is the possibility of _Sharing Eloquent models_ across users.

## Changelog

See the [Changelog](https://github.com/artkonekt/acl/Changelog.md) for more information about what has changed recently.


---

**Next**: [Installation &raquo;](installation.md)
