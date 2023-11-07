<?php

declare(strict_types=1);

namespace Konekt\Acl\Traits;

use Illuminate\Support\Collection;
use Konekt\Acl\Contracts\Permission;
use Konekt\Acl\Contracts\Role;
use Konekt\Acl\Exceptions\GuardDoesNotMatch;
use Konekt\Acl\Exceptions\PermissionDoesNotExist;
use Konekt\Acl\Guard;
use Konekt\Acl\Models\PermissionProxy;
use Konekt\Acl\PermissionRegistrar;

trait HasPermissions
{
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
        if (1 === func_num_args() && is_array($permissions[0])) {
            $permissions = $permissions[0];
        }
        $this->permissions()->detach();

        return $this->givePermissionTo(...$permissions);
    }

    public function revokePermissionTo(string|Permission ...$permissions): static
    {
        $this->permissions()->detach($this->getStoredPermissions(...$permissions));

        $this->forgetCachedPermissions();

        return $this;
    }

    public function forgetCachedPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function getStoredPermission(string|Permission $permission): ?Permission
    {
        return is_string($permission) ?
            PermissionProxy::findByName($permission, $this->getDefaultGuardName())
            :
            $permission;
    }

    protected function getStoredPermissions(string|Permission ...$permissions): Collection
    {
        return PermissionProxy::whereIn('name', array_map(fn ($p) => $p instanceof Permission ? $p->getName() : $p, $permissions))
                ->whereIn('guard_name', $this->getGuardNames())
                ->get();
    }

    /**
     * @throws \Konekt\Acl\Exceptions\GuardDoesNotMatch
     */
    protected function ensureModelSharesGuard(Role|Permission $roleOrPermission): void
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
