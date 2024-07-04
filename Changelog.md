# Konekt Acl Changelog

## 2.0.0
##### 2024-02-17

- Dropped PHP 8.1 support
- Added the `Ownable` interface to be used by Models
- Added the `HasRoles::havingPermission()` query scope that accept a single `string|Permission` parameter
- Added the `HasRoles::havingRole()` query scope that accept a single `string|Role` parameter
- BC: The 'Permission::findByName()' method no longer throws an exception when a permission was not found, but returns NULL instead
- BC: The 'Role::findByName()' method no longer throws an exception when a role was not found, but returns NULL instead
- BC: The 'Role::findById()' method no longer throws an exception when a role was not found, but returns NULL instead
- BC: The `givePermissionTo()` method no longer accepts an array or a Collection parameter, only a variadic `string|Permission` parameter list
- BC: The `HasRoles::permission()` query scope has been renamed to `havingPermissions()`
- BC: The renamed `havingPermissions()` scope no longer accepts and array or Collection, only a variadic `string|Permission` parameter list
- BC: The `HasRoles::role()` query scope has been renamed to `havingRoles()`
- BC: Added the `getName()` method to the `Permission` and `Role` interfaces

# 1.x Series

## 1.8.0
##### 2024-02-17

- Dropped Laravel 9 support
- Dropped PHP 8.0 support
- Added Laravel 11 support

## 1.7.1
##### 2023-11-17

- Added PHP 8.3 support

## 1.7.0
##### 2023-02-17

- Added PHP 8.2 support
- Added Laravel 10 support
- Dropped PHP 7.3 & 7.4 support
- Dropped Laravel 6-8 support

## 1.6.0
##### 2022-02-22

- Added PHP 8.1 support*
- Added Laravel 9 support

> *: Note that PHP 8.1 + Laravel 7.30 or 6.20 aren't supported 

## 1.5.0
##### 2020-12-07

- Added PHP 8 support
- Dropped PHP 7.2 support
- Dropped Laravel 5 support

## 1.4.1
##### 2020-10-31

- Replaced the injection of `cache.store` to `cache` (CacheManager) to improve compatibility with
  packages manipulating the cache,
  eg. [Tenancy for Laravel](https://tenancyforlaravel.com/docs/v3/configuration#cache)

## 1.4.0
##### 2020-09-13

- Added Laravel 8 support

## 1.3.0
##### 2020-03-14

- Added Laravel 7 support
- Added PHP 7.4 support
- Dropped PHP 7.1 support
- Minimum required Concord version is 1.5+

## 1.2.0
##### 2019-11-23

- Added Laravel 5.8 & 6 support
- Removed Laravel 5.4 support
- Minimum required Concord version is 1.4+

## 1.1.0
##### 2018-11-01

- Added clear cache command
- Laravel 5.7 compatibility

## 1.0.0
##### 2018-02-18

- Ported most of Spatie's 2.9.0
- Laravel 5.6 compatibility
- Concord v1.1

# 0.9

## 0.9.0
##### 2017-12-11

- Initial working version based on Spatie's v2.1.6 & v2.7.0
- Now it is a Concord module, using Concord's facilities for provider registration, model management, etc
