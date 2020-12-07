# Konekt Acl Changelog

# 1.x Series

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
