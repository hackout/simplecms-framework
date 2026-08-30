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
    /**
     * 获取所有设置
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return Collection
     */
    public function getConfigs(): Collection
    {
        return SystemConfig::orderBy('sort_order', 'DESC')->get();
    }

    /**
     * 获取指定配置项的值
     *
     * @param string $code
     * @return mixed
     */
    public function getConfigValue(string $code, array $arguments)
    {
        if ($config = SystemConfig::where('code', $code)->first()) {
            return $config->value;
        }
        if (empty($arguments))
            return null;
        return head($arguments);
    }

    /**
     * Magic method to handle dynamic method calls
     *
     * @param string $action
     * @param mixed $arguments
     * @return mixed
     */
    public function __call(string $action, $arguments)
    {
        if (strpos($action, 'get') === 0) {
            $property = Str::snake(Str::replaceFirst('get', '', $action));
            return $this->getConfigValue($property, $arguments);
        }
    }
}