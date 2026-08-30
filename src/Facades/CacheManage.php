<?php

namespace SimpleCMS\Framework\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SimpleCMS\Framework\Packages\System\Cache
 */
class CacheManage extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cache_manage';
    }
}
