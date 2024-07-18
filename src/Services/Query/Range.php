<?php
namespace SimpleCMS\Framework\Services\Query;

use function is_array;
use Illuminate\Database\Query\Builder;

class Range
{
    /**
     * 数字字符范围查询
     * 
     * 说明:
     * 
     * $isFull 如果为真则需要所有字段均出现该关键词
     * 
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array|string $value
     * @param  array|string $fields
     * @param  bool $isFull 
     * @return array
     */
    public static function builder(array|string $value, array|string $fields, bool $isFull = false): array
    {
        $values = gettype($value) == 'array' ? $value : [trim($value), null];
        $fields = gettype($fields) == 'array' ? $fields : [$fields];

        if (head($values) === null && last($values) === null) {
            return [];
        }

        return [
            self::buildQueryFunction($values, $fields, $isFull)
        ];
    }

    private static function buildQueryFunction(array $values, array $fields, bool $isFull): callable
    {
        return function (Builder $query) use ($values, $fields, $isFull) {
            $condition = Condition::range($values);
            $data = self::parseData($values, $condition);

            foreach ($fields as $key => $field) {
                BuilderQuery::applyQuery($query, $field, $condition, $data, $isFull, $key);
            }
        };
    }

    private static function parseData(array $values, string $condition): mixed
    {
        $data = $values;

        if (in_array($condition, ['<', '>='])) {
            $data = is_string(last($values)) ? (float) last($values) : last($values);
        }

        if (is_array($data)) {
            $data = array_map(fn($item) => is_string(last($values)) ? $item : (float) $item, $data);
        }

        return $data;
    }
}