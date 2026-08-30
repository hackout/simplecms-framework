<?php
namespace SimpleCMS\Framework\Services\Query;

use Illuminate\Database\Query\Builder;

class In
{
    /**
     * IN筛选查询
     * 
     * 说明:
     * $isFull 如果为真则需要所有字段均出现该关键词
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array|string       $values
     * @param  array|string       $fields
     * @param  bool               $isFull 
     * @return array
     */
    public static function builder(array|string $values, array|string $fields, bool $isFull = false): array
    {
        $values = gettype($values) != 'array' ? [$values] : $values;
        $fields = gettype($fields) != 'array' ? [$fields] : $fields;

        return [
            self::buildQueryFunction($values, $fields, $isFull)
        ];
    }

    private static function buildQueryFunction(array $values, array $fields, bool $isFull): callable
    {
        return function (Builder $query) use ($values, $fields, $isFull) {
            $data = self::parseData($values);
            foreach ($fields as $key => $field) {
                BuilderQuery::applyQuery($query, $field, 'in', $data, $isFull, $key);
            }
        };
    }

    private static function parseData(array $values): array
    {
        $result = [];
        foreach($values as $value)
        {
            $result[] = trim($value);
        }
        return $result;
    }
}