<?php

namespace SimpleCMS\Framework\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SimpleCMS\Framework\Packages\ExcelPlus\Convert
 */
class ExcelConvert extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'excel_convert';
    }
}
