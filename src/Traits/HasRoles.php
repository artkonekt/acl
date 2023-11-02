<?php

declare(strict_types=1);

namespace Konekt\Acl\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Konekt\Acl\Contracts\Permission;
use Konekt\Acl\Contracts\Role;
use Konekt\Acl\Exceptions\PermissionDoesNotExist;
use Konekt\Acl\Exceptions\RoleDoesNotExist;
use Konekt\Acl\Models\PermissionProxy;
use Konekt\Acl\Models\RoleProxy;

/**
 *
 * @property-read \Illuminate\Database\Eloquent\Collection $roles
 * @property-read \Illuminate\Database\Eloquent\Collection $permissions
 *
 * @method static havingPermission(string|Permission $permission): Builder
 * @method static havingPermissions(string|Permission ...$permissions): Builder
 * @method static havingRole(string|Role $role): Builder
 * @method static havingRoles(string|Role ...$roles): Builder
 */
trait HasRoles
{
    use HasPermissions;

    public static function bootHasRoles(): void
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }
            $model->roles()->detach();
            $model->permissions()->detach();
        });
    }

    public function roles(): MorphToMany
    {
        return $this->morphToMany(RoleProxy::modelClass(), 'model', 'model_roles', 'model_id', 'role_id');
    }

    public function permissions(): MorphToMany
    {
        return $this->morphToMany(PermissionProxy::modelClass(), 'model', 'model_permissions', 'model_id', 'permission_id');
    }

    public function scopeHavingRole(Builder $query, string|Role $role): Builder
    {
        return $this->scopeHavingRoles($query, $role);
    }

    public function scopeHavingRoles(Builder $query, string|Role ...$roles): Builder
    {
        $roles = array_map(function ($role) {
            $result = $role instanceof Role ? $role : RoleProxy::findByName($role, $this->getDefaultGuardName());
            if (null === $result) {
                throw RoleDoesNotExist::named($role);
            }

            return $result;
        }, $roles);

        return $query->whereHas('roles', function ($query) use ($roles) {
            $query->where(function ($query) use ($roles) {
                foreach ($roles as $role) {
                    $query->orWhere('roles.id', $role->id);
                }
            });
        });
    }

    public function scopeHavingPermission(Builder $query, string|Permission $permission): Builder
    {
        return $this->scopeHavingPermissions($query, $permission);
    }

    public function scopeHavingPermissions(Builder $query, string|Permission ...$permissions): Builder
    {
        $permissions = $this->convertToPermissionModels(...$permissions);

        $rolesWithPermissions = array_unique(array_reduce($permissions, function ($result, $permission) {
            return array_merge($result, $permission->roles->all());
        }, []));

        return $query->
        where(function ($query) use ($permissions, $rolesWithPermissions) {
            $query->whereHas('permissions', function ($query) use ($permissions) {
                $query->where(function ($query) use ($permissions) {
                    foreach ($permissions as $permission) {
                        $query->orWhere('permissions.id', $permission->id);
                    }
                });
            });
            if (count($rolesWithPermissions) > 0) {
                $query->orWhereHas('roles', function ($query) use ($rolesWithPermissions) {
                    $query->where(function ($query) use ($rolesWithPermissions) {
                        foreach ($rolesWithPermissions as $role) {
                            $query->orWhere('roles.id', $role->id);
                        }
                    });
                });
            }
        });
    }

    public function assignRole(string|int|Role $role): static
    {
        return $this->assignRoles($role);
    }

    public function assignRoles(string|int|Role ...$roles): static
    {
        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                return $this->getStoredRole($role);
            })
            ->each(function ($role) {
                $this->ensureModelSharesGuard($role);
            })
            ->all();

        $this->roles()->saveMany($roles);

        $this->forgetCachedPermissions();

        return $this;
    }

    public function removeRole(string|int|Role $role): void
    {
        $this->roles()->detach($this->getStoredRole($role));
    }

    public function syncRoles(string|int|Role ...$roles): static
    {
        $this->roles()->detach();

        return $this->assignRoles(...$roles);
    }

    /**
     * Determine if the model has (one of) the given role(s).
     */
    public function hasRole(string|array|Role|Collection $roles): bool
    {
        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->id);
        }

        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }

            return false;
        }

        return $roles->intersect($this->roles)->isNotEmpty();
    }

    /**
     * Determine if the model has any of the given role(s). An alias to `hasRole()`
     */
    public function hasAnyRole(string|array|Role|Collection $roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Determine if the model has all the given role(s).
     */
    public function hasAllRoles(string|array|Role|Collection $roles): bool
    {
        if (is_string($roles) && str_contains($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->id);
        }

        $roles = collect()->make($roles)->map(function ($role) {
            return $role instanceof Role ? $role->name : $role;
        });

        return $roles->intersect($this->roles->pluck('name')) == $roles;
    }

    public function hasPermissionTo(string|Permission $permission, ?string $guardName = null): bool
    {
        if (is_string($permission)) {
            $name = $permission;
            if (null === $permission = PermissionProxy::findByName($name, $guardName ?? $this->getDefaultGuardName())) {
                throw PermissionDoesNotExist::create($name, $guardName ?? $this->getDefaultGuardName());
            }
        }

        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }

    /**
     * Determine if the model has any of the given permissions.
     *
     * @param array ...$permissions
     *
     * @return bool
     */
    public function hasAnyPermission(...$permissions): bool
    {
        if (is_array($permissions[0])) {
            $permissions = $permissions[0];
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the model has the given permission.
     *
     * @param string|Permission $permission
     *
     * @return bool
     */
    public function hasDirectPermission(string|Permission $permission): bool
    {
        if (is_string($permission)) {
            $permission = PermissionProxy::findByName($permission, $this->getDefaultGuardName());

            if (! $permission) {
                return false;
            }
        }

        return $this->permissions->contains('id', $permission->id);
    }

    /**
     * Return all permissions the directory coupled to the model.
     */
    public function getDirectPermissions(): Collection
    {
        return $this->permissions;
    }

    /**
     * Return all the permissions the model has via roles.
     */
    public function getPermissionsViaRoles(): Collection
    {
        return $this->load('roles', 'roles.permissions')
            ->roles->flatMap(function ($role) {
                return $role->permissions;
            })->sort()->values();
    }

    /**
     * Return all the permissions the model has, both directly and via roles.
     */
    public function getAllPermissions(): Collection
    {
        return $this->permissions
            ->merge($this->getPermissionsViaRoles())
            ->sort()
            ->values();
    }

    public function getRoleNames(): Collection
    {
        return $this->roles->pluck('name');
    }

    protected function convertToPermissionModels(string|Permission ...$permissions): array
    {
        return array_filter(
            array_map(
                function (string|Permission $permission) {
                    $result = is_string($permission) ? PermissionProxy::findByName($permission, $this->getDefaultGuardName()) : $permission;
                    if (null === $result) {
                        throw PermissionDoesNotExist::create($permission, $this->getDefaultGuardName());
                    }

                    return $result;
                },
                $permissions
            )
        );
    }

    protected function hasPermissionViaRole(Permission $permission): bool
    {
        return $this->hasRole($permission->roles);
    }

    protected function getStoredRole(string|int|Role $role): Role
    {
        $result = match (true) {
            $role instanceof Role => $role,
            is_numeric($role) => RoleProxy::findById($role, $this->getDefaultGuardName()),
            default => RoleProxy::findByName($role, $this->getDefaultGuardName()),
        };

        if (null === $result) {
            throw is_numeric($role) ? RoleDoesNotExist::withId((int) $role) : RoleDoesNotExist::named($role);
        }

        return $result;
    }

    protected function convertPipeToArray(string $pipeString): string|array
    {
        $pipeString = trim($pipeString);

        if (strlen($pipeString) <= 2) {
            return $pipeString;
        }

        $quoteCharacter = substr($pipeString, 0, 1);
        $endCharacter = substr($quoteCharacter, -1, 1);

        if ($quoteCharacter !== $endCharacter) {
            return explode('|', $pipeString);
        }

        if (! in_array($quoteCharacter, ["'", '"'])) {
            return explode('|', $pipeString);
        }

        return explode('|', trim($pipeString, $quoteCharacter));
    }
}
