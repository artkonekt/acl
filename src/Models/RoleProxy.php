<?php

declare(strict_types=1);

/**
 * Contains the RoleProxy class.
 *
 * @copyright   Copyright (c) 2017 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2017-06-07
 *
 */

namespace Konekt\Acl\Models;

use Konekt\Concord\Proxies\ModelProxy;

/**
 * @method static null|\Konekt\Acl\Contracts\Role findByName(string $name, ?string $guardName = null)
 * @method static null|\Konekt\Acl\Contracts\Role findById(int|string $id, ?string $guardName = null)
 */
class RoleProxy extends ModelProxy
{
}
