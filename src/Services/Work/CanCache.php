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
    public function run(mixed $model): mixed
    {
        $className = get_class($model);
        return defined($className . '::SERVICE_CACHE') ? $className::SERVICE_CACHE : true;
    }
}