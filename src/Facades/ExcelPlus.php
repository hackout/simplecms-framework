<?php

namespace SimpleCMS\Framework\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SimpleCMS\Framework\Packages\ExcelPlus
 */
class ExcelPlus extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'excel_plus';
    }
}
