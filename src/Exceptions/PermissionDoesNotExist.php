<?php

namespace Konekt\Acl\Exceptions;

use InvalidArgumentException;

class PermissionDoesNotExist extends InvalidArgumentException
{
    public static function create(string $permissionName)
    {
        return new static("There is no permission named `{$permissionName}`.");
    }
}
