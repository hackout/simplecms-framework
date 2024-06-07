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
    public function __construct(protected mixed $model)
    {
    }

    public function run():bool
    {
        return $this->model && method_exists($this->model, 'media');
    }
}