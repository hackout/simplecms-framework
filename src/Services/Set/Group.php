<?php
namespace SimpleCMS\Framework\Services\Set;

use function is_array;
use function is_string;
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
        if (is_string($value)) {
            $result[] = $value;
        }
        if (is_array($value)) {
            $result = $value;
        }
        if ($value instanceof Expression) {
            $result[] = $value;
        }
        return $result;
    }
}