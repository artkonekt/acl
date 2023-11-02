<?php

declare(strict_types=1);

namespace Konekt\Acl\Traits;

use Illuminate\Support\Collection;
use Konekt\Acl\Contracts\Permission;
use Konekt\Acl\Exceptions\GuardDoesNotMatch;
use Konekt\Acl\Exceptions\PermissionDoesNotExist;
use Konekt\Acl\Guard;
use Konekt\Acl\Models\PermissionProxy;
use Konekt\Acl\PermissionRegistrar;

trait HasPermissions
{
    /**
     * Grant the given permission(s) to a role.
     *
     * @param string|array|Permission|\Illuminate\Support\Collection $permissions
     *
     * @return $this
     */
    public function givePermissionTo(string|Permission ...$permissions): static
    {
        $normalized = [];
        foreach ($permissions as $permission) {
            if (null === $model = (is_string($permission) ? $this->getStoredPermission($permission) : $permission)) {
                throw PermissionDoesNotExist::create($permission);
            }

            $this->ensureModelSharesGuard($model);
            $normalized[] = $model;
        }

        $this->permissions()->saveMany($normalized);

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * Remove all current permissions and set the given ones.
     */
    public function syncPermissions(...$permissions): static
    {
        $this->permissions()->detach();

        return $this->givePermissionTo(...$permissions);
    }

    /**
     * Revoke the given permission.
     *
     * @param Permission|Permission[]|string|string[] $permission
     *
     * @return $this
     */
    public function revokePermissionTo($permission): static
    {
        $this->permissions()->detach($this->getStoredPermission($permission));

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * Forget the cached permissions.
     */
    public function forgetCachedPermissions()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @param string|array|Permission|\Illuminate\Support\Collection $permissions
     *
     * @return Permission|Permission|\Illuminate\Support\Collection
     */
    protected function getStoredPermission($permissions)
    {
        if (is_string($permissions)) {
            return PermissionProxy::findByName($permissions, $this->getDefaultGuardName());
        }

        if (is_array($permissions)) {
            return PermissionProxy::whereIn('name', $permissions)
                ->whereIn('guard_name', $this->getGuardNames())
                ->get();
        }

        return $permissions;
    }

    /**
     * @param Permission|\Konekt\Acl\Contracts\Role $roleOrPermission
     *
     * @throws \Konekt\Acl\Exceptions\GuardDoesNotMatch
     */
    protected function ensureModelSharesGuard($roleOrPermission)
    {
        if (! $this->getGuardNames()->contains($roleOrPermission->guard_name)) {
            throw GuardDoesNotMatch::create($roleOrPermission->guard_name, $this->getGuardNames());
        }
    }

    protected function getGuardNames(): Collection
    {
        return Guard::getNames($this);
    }

    protected function getDefaultGuardName(): string
    {
        return Guard::getDefaultName($this);
    }
}
