<?php
namespace SimpleCMS\Framework\Packages\System;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
use SimpleCMS\Framework\Models\SystemConfig;

/**
 * 系统设置
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class Config
{

    protected Collection $configs;

    public function __construct()
    {
        $this->configs = SystemConfig::get();
    }

    /**
     * 获取所有设置
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return Collection
     */
    public function getConfigs(): Collection
    {
        return $this->configs;
    }

    public function __call(string $action, $arguments)
    {
        if (strpos($action, 'get') === 0) {
            $property = Str::snake(Str::replaceFirst('get', '', $action));
            if ($config = $this->configs->where('code', $property)->first()) {
                return $config->value;
            }
        }
    }
}