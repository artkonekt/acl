<?php

declare(strict_types=1);

namespace Konekt\Acl\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface Permission
{
    public function roles(): BelongsToMany;

    public function getName(): string;

    public static function findByName(string $name, ?string $guardName = null): ?self;
}
