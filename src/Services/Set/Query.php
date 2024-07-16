<?php
namespace SimpleCMS\Framework\Services\Set;

use function is_array;
use function is_callable;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Query\Expression as QueryRaw;

class Query
{
    public static function run(array $data = [], callable|null|array|Expression|string $value = null): array
    {
        if (!$value)
            return [];
        foreach (static::valueToArray($value) as $rs) {
            $data[] = $rs;
        }
        return $data;
    }

    private static function valueToArray(callable|array|Expression|string $value): array
    {
        $result = [];
        if (is_callable($value)) {
            $result[] = [$value];
        } else if (is_array($value)) {
            if (!Arr::isList($value)) {
                foreach ($value as $key => $val) {
                    $result[] = [$key, '=', $val];
                }
            } else if (is_callable(head($value))) {
                $result = $value;
            } else {
                $result[] = $value;
            }
        } elseif ($value instanceof Expression) {
            $result[] = [$value];
        } else {
            $result[] = [new QueryRaw($value)];
        }
        return $result;
    }
}