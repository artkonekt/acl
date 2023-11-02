<?php

declare(strict_types=1);

namespace Konekt\Acl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Konekt\Acl\Contracts\Permission as PermissionContract;
use Konekt\Acl\Contracts\Role as RoleContract;
use Konekt\Acl\Exceptions\GuardDoesNotMatch;
use Konekt\Acl\Exceptions\PermissionDoesNotExist;
use Konekt\Acl\Exceptions\RoleAlreadyExists;
use Konekt\Acl\Guard;
use Konekt\Acl\Traits\HasPermissions;
use Konekt\Acl\Traits\RefreshesPermissionCache;

/**
 * @property string $name
 */
class Role extends Model implements RoleContract
{
    use HasPermissions;
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

        if (static::where('name', $attributes['name'])->where('guard_name', $attributes['guard_name'])->first()) {
            throw RoleAlreadyExists::create($attributes['name'], $attributes['guard_name']);
        }

        return static::query()->create($attributes);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(PermissionProxy::modelClass(), 'role_permissions');
    }

    /**
     * A role belongs to some users of the model associated with its guard.
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            getModelForGuard($this->attributes['guard_name']),
            'model',
            'model_roles',
            'role_id',
            'model_id'
        );
    }

    public static function findByName(string $name, ?string $guardName = null): ?RoleContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        return static::where('name', $name)
            ->where('guard_name', $guardName ?? Guard::getDefaultName(static::class))
            ->first();
    }

    public static function findById(int $id, ?string $guardName = null): ?RoleContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        return static::where('id', $id)
            ->where('guard_name', $guardName ?? Guard::getDefaultName(static::class))
            ->first();
    }

    public function hasPermissionTo(string|PermissionContract $permission): bool
    {
        if (is_string($permission)) {
            $name = $permission;
            $permission = PermissionProxy::findByName($name, $this->getDefaultGuardName());
            if (null === $permission) {
                throw PermissionDoesNotExist::create($name, $this->getDefaultGuardName());
            }
        }

        if ($this->getGuardNames()->doesntContain($permission->guard_name)) {
            throw GuardDoesNotMatch::create($permission->guard_name, $this->getGuardNames());
        }

        return $this->permissions->contains('id', $permission->id);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
