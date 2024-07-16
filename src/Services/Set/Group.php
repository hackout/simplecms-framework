<?php
namespace SimpleCMS\Framework\Services\Set;

use function is_array;
use Illuminate\Contracts\Database\Query\Expression;

class Group
{
    public static function run(array $data = [], null|array|Expression|string $value = null): array
    {
        if (!$value)
            return [];
        foreach (static::valueToArray($value) as $rs) {
            $data[] = $rs;
        }
        return $data;
    }

    private static function valueToArray(array|Expression|string $value = null): array
    {
        $result = [];
        if (is_array($value)) {
            $result = $value;
        } elseif ($value instanceof Expression) {
            $result[] = $value;
        } elseif (!empty($value)) {
            $result[] = $value;
        }
        return $result;
    }
}