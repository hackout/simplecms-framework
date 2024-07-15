<?php
namespace SimpleCMS\Framework\Services\Work;


/**
 * 是否允许缓存
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 */
class CanCache
{
    public static function run(string $model): mixed
    {
        return defined($model . '::SERVICE_CACHE') ? $model::SERVICE_CACHE : true;
    }
}