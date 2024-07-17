<?php
namespace SimpleCMS\Framework\Services\Query;

use Carbon\Carbon;
use function is_array;
use function is_string;
use Illuminate\Database\Query\Builder;

class DateTimeRange
{
    /**
     * 时间范围
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
        $values = is_array($value) ? $value : [trim($value), null];
        $fields = is_array($fields) ? $fields : [$fields];

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
            $data = is_string(last($values)) ? Carbon::parse(last($values)) : last($values);
        }

        if (is_array($data)) {
            $data = array_map(fn($item) => $item instanceof Carbon ? $item : Carbon::parse($item), $data);
        }

        return $data;
    }

}