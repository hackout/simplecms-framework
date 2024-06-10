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

    protected function convertContent(SystemConfig $systemConfig)
    {
        switch ($systemConfig->type) {
            case 'switch':
                $content = $systemConfig->content == 1;
                break;
            case 'list':
            case 'checkbox':
                $content = json_decode($systemConfig->content, true);
                break;
            case 'radio':
            case 'select':
                $content = intval($systemConfig->content);
                break;
            case 'file':
            case 'image':
                $content = $systemConfig->file;
                break;
            default:
                $content = $systemConfig->content;
                break;
        }
        return $content;
    }

    public function __call(string $action, $arguments)
    {
        if (strpos($action, 'get') === 0) {
            $property = Str::snake(Str::replaceFirst('get', '', $action));
            if ($config = $this->configs->where('code', $property)->first()) {
                return $this->convertContent($config);
            }
        }
    }
}