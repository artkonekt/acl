<?php

declare(strict_types=1);

namespace Konekt\Acl\Traits;

use Konekt\Acl\PermissionRegistrar;

trait RefreshesPermissionCache
{
    public static function bootRefreshesPermissionCache()
    {
        static::saved(function () {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });

        static::deleted(function () {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });
    }
}
