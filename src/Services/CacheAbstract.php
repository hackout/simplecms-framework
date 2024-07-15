<?php

namespace SimpleCMS\Framework\Services;


abstract class CacheAbstract extends BaseService
{

    /**
     * @param array|null $array
     * @return string
     */
    abstract function getCacheName(array|null $array = null): string;

    /**
     * 读取数据缓存
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array    $array
     * @param  callable $function
     * @return mixed
     */
    abstract function getCacheData(array $array, callable $function): mixed;

    /**
     * 清空缓存标记
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    abstract function clearCacheData();

    /**
     * 清空缓存数据
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    abstract function clearCache();

    /**
     * @param  array  $array
     * @return string
     */
    abstract function getCacheKey(array $array): string;
}
