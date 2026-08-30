<?php
namespace SimpleCMS\Framework\Services\Set;

use Illuminate\Contracts\Database\Query\Expression;

class Group
{
    public static function run(array $data = [], null|array|Expression|string $value = null): array
    {
        if (empty($value))
            return [];
        foreach (static::valueToArray($value) as $rs) {
            $data[] = $rs;
        }
        return $data;
    }

    private static function valueToArray(array|Expression|string|null $value = null): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Expression) {
            return [$value];
        }

        return [$value];
    }
}