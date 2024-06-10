<?php
namespace SimpleCMS\Framework\Packages\System;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache as BaseCache;
use Illuminate\Support\Facades\DB;

/**
 * 缓存管理
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class Cache
{

    /**
     * 获取缓存占用量
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    public function size(): array
    {
        set_time_limit(0);
        $type = config('cache.default', 'file');
        $cacheData = [
            'size' => 'N/A',
            'total' => 'N/A'
        ];
        switch ($type) {
            case 'redis':
                $cacheData = $this->getRedisSize();
                break;
            case 'array':
                $cacheData = $this->getArrayCacheSize();
                break;
            case 'database':
                $cacheData = $this->getDatabaseCacheSize();
                break;
            case 'file':
                $cacheData = $this->getFileSize();
                break;
        }
        if ($type == 'redis') {
            return $this->getRedisSize();
        }
        return $this->getFileSize();
    }

    /**
     * 获取File缓存占用
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    protected function getFileSize(): array
    {
        $size = 0;
        $total = 0;
        $storage = BaseCache::getStore();
        $filesystem = $storage->getFilesystem();
        $dir = BaseCache::getDirectory();
        foreach ($filesystem->allFiles($dir) as $file1) {
            if (is_dir($file1->getPath())) {
                foreach ($filesystem->allFiles($file1->getPath()) as $file2) {
                    $size += $file2->getSize();
                    $total++;
                }
            }
        }
        return [
            'size' => $size,
            'total' => $total
        ];
    }

    /**
     * 获取Redis缓存占用
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    protected function getRedisSize(): array
    {
        $redis = Redis::connection();
        $size = 0;
        $total = 0;
        for ($i = 0; $i < 16; $i++) {
            $db = $redis->select($i);
            $size += $db->getCacheSize();
            $keys = $db->keys("*");
            $total += count($keys);
        }
        return [
            'size' => $size,
            'total' => $total
        ];
    }

    /**
     * 获取Array缓存占用量和条数
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    protected function getArrayCacheSize(): array
    {
        $size = memory_get_usage();

        return [
            'size' => $size,
            'total' => 'N/A'
        ];
    }

    /**
     * 获取Database缓存占用量和条数
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    protected function getDatabaseCacheSize(): array
    {
        $db = DB::connection(config('cache.stores.database.connection'))
            ->table(config('cache.stores.database.table'));
        $size = $db->selectRaw('SUM(LENGTH(`key`) + LENGTH(`value`)+ LENGTH(`expiration`))')->first();
        $total = $db->count();
        return [
            'size' => $size,
            'total' => $total
        ];
    }

    /**
     * 清空系统缓存
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    public function clear(): void
    {
        set_time_limit(0);
        BaseCache::clear();
    }
}