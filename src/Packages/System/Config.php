<?php
namespace SimpleCMS\Framework\Packages\System;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
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

    /**
     * 保存设置
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function setConfig(string $key, $value)
    {
        $config = $this->configs->where('code', $key)->first();
        switch ($config->type) {
            case 'input':
            case 'textarea':
            case 'editor':
                $config->update(['content' => trim($value)]);
                break;
            case 'image':
            case 'file':
                $config->media->each(fn($media) => $media->delete());
                if ($value instanceof UploadedFile) {
                    $config->addMedia($value)->toMediaCollection(SystemConfig::MEDIA_FILE);
                }
                if (is_base_image($value)) {
                    $config->addMediaFromBase64($value)->toMediaCollection(SystemConfig::MEDIA_FILE);
                }
                if (is_url($value)) {
                    $config->addMediaFromUrl($value)->toMediaCollection(SystemConfig::MEDIA_FILE);
                }
            case 'switch':
                $config->update(['content' => $value ? 1 : 0]);
                break;
            case 'radio':
            case 'select':
                $config->update(['content' => $config->options && array_key_exists($value, $config->options) ? $value : 0]);
                break;
            case 'list':
                $config->update(['content' => Str::isJson($value) ? $value : json_encode($value)]);
                break;
            case 'checkbox':
                $values = [];
                if (is_array($config->options)) {
                    $values = [];
                    $config->options->map(function ($n, $key) use (&$values, $value) {
                        if (in_array($key, $value)) {
                            $values[] = $key;
                        }
                    });
                }
                $config->update(['content' => json_encode($values)]);
                break;
        }
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