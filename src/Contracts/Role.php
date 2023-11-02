<?php

namespace Konekt\Acl\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Konekt\Acl\Exceptions\RoleDoesNotExist;

interface Role
{
    public function permissions(): BelongsToMany;

    public static function findByName(string $name, ?string $guardName = null): ?self ;

    public static function findById(int $id, ?string $guardName = null): ?self;

    public function hasPermissionTo(string|Permission $permission): bool;
}
