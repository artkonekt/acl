<?php
/**
 * Contains the InteractsWithAclCache trait.
 *
 * @copyright   Copyright (c) 2018 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2018-11-01
 *
 */

namespace Konekt\Acl\Test\Concerns;

use Illuminate\Support\Facades\DB;
use Konekt\Acl\PermissionRegistrar;

trait InteractsWithAclCache
{
    protected $queriesPerCacheProvision = 2;

    /** @var PermissionRegistrar */
    protected $registrar;

    protected function setUpCacheTest()
    {
        $this->registrar = app(PermissionRegistrar::class);
        $this->registrar->forgetCachedPermissions();
        DB::connection()->enableQueryLog();
    }

    protected function assertQueryCount(int $expected)
    {
        $this->assertCount($expected, DB::getQueryLog());
    }

    protected function resetQueryCount()
    {
        DB::flushQueryLog();
    }
}
