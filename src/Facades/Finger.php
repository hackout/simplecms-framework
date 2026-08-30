<?php

namespace SimpleCMS\Framework\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SimpleCMS\Framework\Packages\Finger\Finger
 */
class Finger extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'finger';
    }
}
