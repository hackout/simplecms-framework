<?php
namespace SimpleCMS\Framework\Packages\System;

use function phpversion;
use Illuminate\Support\{Number,Collection};
use Illuminate\Support\Facades\{Request,DB};

/**
 * 系统环境信息
 * 
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
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
        $serverInfo = collect();
        // 获取服务器信息
        $server = collect();
        $server->put(/** @scrutinizer ignore-type */ 'name',/** @scrutinizer ignore-type */ (string) Request::server('SERVER_NAME'));
        $server->put(/** @scrutinizer ignore-type */ 'software',/** @scrutinizer ignore-type */ (string) Request::server('SERVER_SOFTWARE'));
        $server->put(/** @scrutinizer ignore-type */ 'ip',/** @scrutinizer ignore-type */ (string) Request::server('SERVER_ADDR'));
        $server->put(/** @scrutinizer ignore-type */ 'port',/** @scrutinizer ignore-type */ (string) Request::server('SERVER_PORT'));
        $serverInfo->put(/** @scrutinizer ignore-type */ 'server',/** @scrutinizer ignore-type */ $server);

        // 获取 PHP 版本信息
        $serverInfo->put(/** @scrutinizer ignore-type */ 'php',/** @scrutinizer ignore-type */ phpversion());

        // 获取数据库信息（需要先配置数据库连接）
        $dbInfo = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);

        $serverInfo->put(/** @scrutinizer ignore-type */ 'database',/** @scrutinizer ignore-type */ $dbInfo);

        // 获取服务器的 CPU 信息
        $serverInfo->put(/** @scrutinizer ignore-type */ 'cpu',/** @scrutinizer ignore-type */ $this->getCpuInfo());

        // 获取服务器的系统版本信息
        $systemVersion = php_uname('a');
        $serverInfo->put(/** @scrutinizer ignore-type */ 'system',/** @scrutinizer ignore-type */ $systemVersion);

        $serverInfo->put(/** @scrutinizer ignore-type */ 'framework',/** @scrutinizer ignore-type */ $this->getFramework());
        $serverInfo->put(/** @scrutinizer ignore-type */ 'laravel', app()->/** @scrutinizer ignore-call */ version());
        return $serverInfo;
    }

    private function getFramework(): array
    {
        $framework = [
            'name' => 'Unknown',
            'version' => 'Unknown',
            'authors' => 'DennisLui<Cdiantong.Com>'
        ];
        $data = json_decode(file_get_contents(__DIR__ . '/../../../composer.json'), true);
        if (isset($data['name']))
            $framework['name'] = $data['name'];
        if (isset($data['version']))
            $framework['version'] = $data['version'];
        if (isset($data['authors']))
            $framework['authors'] = $data['authors'];
        return $framework;
    }

    private function getCpuInfo(): array
    {
        $cpuInfo = [
            'name' => 'Unknown',
            'core' => 0,
            'size' => 0,
            'used' => 0
        ];
        if ($cpuCmd = shell_exec('cat /proc/cpuinfo|grep "model name" && cat /proc/cpuinfo |grep "cache size"')) {
            $cpu = explode(PHP_EOL, $cpuCmd);
            $cpuInfo['used'] = $this->getCpuUsagePercentage();
            foreach ($cpu as $rs) {
                if (strpos($rs, 'model name') === 0) {
                    if (!$cpuInfo['name']) {
                        $cpuInfo['name'] = trim(last(explode(":", $rs)));
                    }
                    $cpuInfo['core']++;
                }
                if (strpos($rs, 'cache size') === 0) {
                    $cpuInfo['size'] += intval(trim(str_replace('KB', '', last(explode(":", $rs)))));
                }
            }
            $cpuInfo['size'] = Number::fileSize($cpuInfo['size'] * 1024 * 1024);
        }
        return $cpuInfo;
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

    /**
     * 获取负载
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return float
     */
    protected function getCpuUsage(): float
    {
        $load = \sys_getloadavg();
        return (float) $load[0];
    }

    /**
     * CPU使用率
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return float
     */
    protected function getCpuUsagePercentage(): float
    {
        $load = $this->getCpuUsage();
        $cpuCount = shell_exec("cat /proc/cpuinfo | grep processor | wc -l");
        return ($load / $cpuCount) * 100;
    }

}