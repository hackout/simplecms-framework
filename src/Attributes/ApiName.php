<?php

namespace SimpleCMS\Framework\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
/**
 * API别名处理
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class ApiName
{
    public function __construct(
        public string $name,
        public array $roles = []
    ) {
    }
}
