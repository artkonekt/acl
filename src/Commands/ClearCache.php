<?php
/**
 * Contains the ClearCache class.
 *
 * @copyright   Copyright (c) 2018 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2018-11-01
 *
 */

namespace Konekt\Acl\Commands;

use Illuminate\Cache\CacheManager;
use Illuminate\Console\Command;
use Konekt\Acl\PermissionRegistrar;

class ClearCache extends Command
{
    protected $signature = 'acl:cache:clear';

    protected $description = 'Clear the ACL cache';

    /**
     * The cache manager instance.
     *
     * @var \Illuminate\Cache\CacheManager
     */
    protected $cache;

    /**
     * Create a new cache clear command instance.
     *
     * @param  \Illuminate\Cache\CacheManager  $cache
     * @return void
     */
    public function __construct(CacheManager $cache)
    {
        parent::__construct();

        $this->cache = $cache;
    }

    public function handle()
    {
        $this->cache->store(null)->forget(PermissionRegistrar::CACHE_KEY);

        $this->info('The ACL cache has been cleared.');
    }
}
