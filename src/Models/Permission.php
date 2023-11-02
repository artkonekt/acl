<?php

declare(strict_types=1);

namespace Konekt\Acl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Konekt\Acl\Contracts\Permission as PermissionContract;
use Konekt\Acl\Exceptions\PermissionAlreadyExists;
use Konekt\Acl\Guard;
use Konekt\Acl\PermissionRegistrar;
use Konekt\Acl\Traits\HasRoles;
use Konekt\Acl\Traits\RefreshesPermissionCache;

class Permission extends Model implements PermissionContract
{
    use HasRoles;
    use RefreshesPermissionCache;

    public $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? config('auth.defaults.guard');

        parent::__construct($attributes);
    }

    public static function create(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? Guard::getDefaultName(static::class);

        if (static::getPermissions()->where('name', $attributes['name'])->where('guard_name', $attributes['guard_name'])->first()) {
            throw PermissionAlreadyExists::create($attributes['name'], $attributes['guard_name']);
        }

        return static::query()->create($attributes);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(RoleProxy::modelClass(), 'role_permissions');
    }

    /**
     * A permission belongs to some users of the model associated with its guard.
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            getModelForGuard($this->attributes['guard_name']),
            'model',
            'model_permissions',
            'permission_id',
            'model_id'
        );
    }

    public static function findByName(string $name, ?string $guardName = null): ?PermissionContract
    {
        return static::getPermissions()
            ->where('name', $name)
            ->where('guard_name', $guardName ?? Guard::getDefaultName(static::class))
            ->first();
    }

    public static function findOrCreate(string $name, ?string $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $permission = static::getPermissions()->where('name', $name)->where('guard_name', $guardName)->first();

        if (! $permission) {
            return static::create(['name' => $name, 'guard_name' => $guardName]);
        }

        return $permission;
    }

    /**
     * Get the current cached permissions.
     */
    protected static function getPermissions(): Collection
    {
        return app(PermissionRegistrar::class)->getPermissions();
    }
}
