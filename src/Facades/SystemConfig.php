<?php

namespace SimpleCMS\Framework\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SimpleCMS\Framework\Packages\System\Config
 */
class SystemConfig extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'system_config';
    }
}
