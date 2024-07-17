<?php

namespace SimpleCMS\Framework\Contracts;


/**
 * @mixin \SimpleCMS\Framework\Services\BaseService
 */
interface CacheInterface
{

    /**
     * @param array|null $array
     * @return string
     */
    public function getCacheName(array|null $array = null): string;

    /**
     * 读取数据缓存
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array    $array
     * @param  callable $function
     * @return mixed
     */
    public function getCacheData(array $array, callable $function): mixed;

    /**
     * 清空缓存标记
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    public function clearCacheData();

    /**
     * 清空缓存数据
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    public function clearCache();

    /**
     * @param  array  $array
     * @return string
     */
    public function getCacheKey(array $array): string;
}
