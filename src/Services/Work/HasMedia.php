<?php
namespace SimpleCMS\Framework\Services\Work;

/**
 * 检查是否附件关联
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 */
class HasMedia
{
    public function run(mixed $model): bool
    {
        return method_exists($model, 'media');
    }
}