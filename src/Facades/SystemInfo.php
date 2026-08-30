<?php

namespace SimpleCMS\Framework\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SimpleCMS\Framework\Packages\System\System
 */
class SystemInfo extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'system_info';
    }
}
