<?php

namespace SimpleCMS\Framework\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SimpleCMS\Framework\Packages\Menu\Menu
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
