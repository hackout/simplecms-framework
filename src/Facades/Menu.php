<?php

namespace SimpleCMS\Framework\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SimpleCMS\Framework\Packages\Dict\Dict
 */
class Menu extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'menu';
    }
}
