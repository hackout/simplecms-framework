<?php
namespace SimpleCMS\Framework\Packages\System;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;

/**
 * 系统环境信息
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class System
{

    protected Collection $system;

    public function __construct()
    {
        $this->system = $this->getServiceInfo();
    }

    protected function getServiceInfo(): Collection
    {
        $serverInfo = [];

        // 获取服务器信息
        $serverInfo['server'] = collect([
            'name' => $_SERVER['SERVER_NAME'] ?? null,
            'software' => $_SERVER['SERVER_SOFTWARE'] ?? null,
            'ip' => $_SERVER['SERVER_ADDR'] ?? null,
            'port' => $_SERVER['SERVER_PORT'] ?? null,
        ]);

        // 获取 PHP 版本信息
        $serverInfo['php'] = [
            'version' => phpversion(),
            'extensions' => get_loaded_extensions(),
        ];

        // 获取数据库信息（需要先配置数据库连接）
        $dbInfo = Artisan::call('db:raw', ['query' => 'SELECT VERSION() as version']);
        $serverInfo['database'] = collect([
            'version' => $dbInfo,
            // 其他数据库相关信息可根据需要添加
        ]);

        // 获取服务器的 CPU 信息
        $cpuInfo = shell_exec('cat /proc/cpuinfo');
        $serverInfo['cpu'] = $cpuInfo;

        // 获取服务器的系统版本信息
        $systemVersion = php_uname('a');
        $serverInfo['system'] = $systemVersion;

        return collect($serverInfo);
    }


    /**
     * 获取所有参数
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return Collection
     */
    public function getSystem(): Collection
    {
        return $this->system;
    }

}