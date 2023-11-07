<?php

declare(strict_types=1);

/**
 * Contains the Ownable interface.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-07
 *
 */

namespace Konekt\Acl\Contracts;

use Illuminate\Contracts\Auth\Access\Authorizable;

interface Ownable
{
    public function getOwner(): Authorizable;

    public function setOwner(Authorizable $user): void;

    public function isOwnedBy(Authorizable $user): bool;
}
