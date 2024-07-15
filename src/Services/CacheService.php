<?php

namespace SimpleCMS\Framework\Services;

use Illuminate\Support\Facades\Cache;

class CacheService extends CacheAbstract
{
    /**
     * @param array|null $array
     * @return string
     */
    public function getCacheName(array|null $array = null): string
    {
        $cacheName = $this->getTableName();
        if ($array) {
            $cacheName .= '_' . $this->getCacheKey($array);
        }
        return $cacheName;
    }

    /**
     * 读取数据缓存
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array    $array
     * @param  callable $function
     * @return mixed
     */
    public function getCacheData(array $array, callable $function): mixed
    {
        if (!(new Work\CanCache)->run($this->model))
            return $function();
        $cacheKeyName = $this->getCacheName($array);
        $cacheName = $this->getCacheName();
        return Cache::rememberForever($cacheKeyName, function () use ($function, $cacheName, $cacheKeyName) {
            $cache = Cache::get($cacheName, []);
            $cache[] = $cacheKeyName;
            Cache::forever($cacheName, $cache);
            return $function();
        });
    }

    /**
     * 清空缓存标记
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    public function clearCacheData()
    {
        $cacheName = $this->getCacheName();
        $cache = Cache::get($cacheName, []);
        foreach ($cache as $key) {
            Cache::forget($key);
        }
        Cache::forget($cacheName);
    }

    /**
     * 清空缓存数据
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    public function clearCache()
    {
        $this->clearCacheData();
    }

    public function getCacheKey(array $array): string
    {
        return md5(json_encode($array));
    }

}
