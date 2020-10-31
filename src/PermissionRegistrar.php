<?php

namespace Konekt\Acl;

use Illuminate\Cache\CacheManager;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Auth\Access\Gate;
use Konekt\Acl\Models\PermissionProxy;
use Illuminate\Contracts\Auth\Authenticatable;
use Konekt\Acl\Exceptions\PermissionDoesNotExist;

class PermissionRegistrar
{
    const CACHE_KEY = 'konekt.acl.cache';

    /** @var \Illuminate\Contracts\Auth\Access\Gate */
    protected $gate;

    /** @var CacheManager */
    protected $cache;

    public function __construct(Gate $gate, CacheManager $cache)
    {
        $this->gate  = $gate;
        $this->cache = $cache;
    }

    public function registerPermissions(): bool
    {
        $this->gate->before(function (Authenticatable $user, string $ability) {
            try {
                if (method_exists($user, 'hasPermissionTo')) {
                    return $user->hasPermissionTo($ability) ?: null;
                }
            } catch (PermissionDoesNotExist $e) {
            }
        });

        return true;
    }

    public function forgetCachedPermissions()
    {
        $this->cache->forget(static::CACHE_KEY);
    }

    public function getPermissions(): Collection
    {
        return $this->cache->remember(static::CACHE_KEY, config('konekt.acl.cache_expiration_time'), function () {
            return PermissionProxy::with('roles')->get();
        });
    }
}
