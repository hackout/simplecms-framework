<?php
namespace SimpleCMS\Framework\Packages\System;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache as BaseCache;
use Illuminate\Filesystem\Filesystem;

/**
 * 缓存管理
 */
class Cache
{
    /**
     * 获取缓存占用量
     *
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

        return $cacheData;
    }

    /**
     * 获取File缓存占用
     *
     * @return array
     */
    protected function getFileSize(): array
    {
        $size = 0;
        $total = 0;
        $storage = BaseCache::getStore();
        $filesystem = new Filesystem();
        $dir = $storage->getDirectory();

        foreach ($filesystem->allFiles($dir) as $file) {
            $size += $file->getSize();
            $total++;
        }

        return [
            'size' => $size,
            'total' => $total
        ];
    }

    /**
     * 获取Redis缓存占用
     *
     * @return array
     */
    protected function getRedisSize(): array
    {
        try {
            $redis = Redis::connection();
            $memoryInfo = $redis->info('memory');
            $size = $memoryInfo['used_memory'] ?? 0;
            $total = $redis->dbSize();

            return [
                'size' => $size,
                'total' => $total
            ];
        } catch (\Exception $e) {
            return [
                'size' => 0,
                'total' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 获取Array缓存占用量和条数
     *
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
     * @return array
     */
    protected function getDatabaseCacheSize(): array
    {
        $db = DB::connection(config('cache.stores.database.connection'))
            ->table(config('cache.stores.database.table'));
        $size = $db->selectRaw('SUM(LENGTH(`key`) + LENGTH(`value`) + LENGTH(`expiration`)) as data_length')->first();
        $total = $db->count();

        return [
            'size' => $size->data_length ?? 0,
            'total' => $total
        ];
    }

    /**
     * 清空系统缓存
     *
     * @return void
     */
    public function clear(): void
    {
        set_time_limit(0);
        BaseCache::clear();
    }
}
