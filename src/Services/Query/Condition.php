<?php
namespace SimpleCMS\Framework\Services\Query;

class Condition
{
    /**
     * 请求参数数量
     * @param string $condition
     * @return int
     */
    public static function paramNumber(string $condition = '='): int
    {
        return match ($condition) {
            'raw' => 1,
            '!=' => 3,
            '<>' => 3,
            '<=' => 3,
            '<' => 3,
            '>=' => 3,
            '>' => 3,
            'like' => 3,
            default => 2
        };
    }

    /**
     * 获取Range类条件
     * @param array $values
     * @return string
     */
    public static function range(array $values): string
    {
        return (head($values) === null) ? '<' : ((last($values) === null) ? '>=' : 'between');
    }
}