<?php
namespace SimpleCMS\Framework\Services\Set;

use function gettype;
use function is_callable;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Query\Expression as QueryRaw;

class Query
{
    public static function run(array $data = [], callable|null|array|Expression|string $value = null): array
    {
        if (empty($value))
            return [];
        foreach (static::valueToArray($value) as $rs) {
            $data[] = $rs;
        }
        return $data;
    }

    private static function valueToArray(callable|array|Expression|string $value): array
    {
        if (is_callable($value) || $value instanceof Expression) {
            return [$value];
        }
        if (gettype($value) == 'string') {
            return [new QueryRaw($value)];
        }
        return self::getArrayValue($value);
    }

    private static function getArrayValue(array $value): array
    {
        if (is_callable(head($value))) {
            return $value;
        }
        if (Arr::isList($value)) {
            return [$value];
        }
        $result = [];
        foreach ($value as $key => $val) {
            $result[] = [$key, '=', $val];
        }
        return $result;

    }
}