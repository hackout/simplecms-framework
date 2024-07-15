<?php
namespace SimpleCMS\Framework\Services\Set;

use function is_array;
use function is_string;
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
        }
        if (is_string($value)) {
            $result[] = [new QueryRaw($value)];
        }
        if (is_array($value)) {
            if (!Arr::isList($value)) {
                foreach ($value as $key => $val) {
                    $result[] = [$key, '=', $val];
                }
            } else if (is_string(head($value))) {
                $result[] = $value;
            } else {
                $result = $value;
            }
        }
        if ($value instanceof Expression) {
            $result[] = [$value];
        }
        return $result;
    }
}