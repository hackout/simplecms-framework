<?php
namespace SimpleCMS\Framework\Services\Query;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;

class Date
{
    /**
     * 时间筛选查询
     * 
     * 说明:
     * 
     * $isFull 如果为真则需要所有字段均出现该关键词
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  Carbon|string      $value
     * @param  array|string       $fields
     * @param  bool               $isFull 
     * @return array
     */
    public static function builder(Carbon|string $value, array|string $fields, bool $isFull = false): array
    {
        $fields = gettype($fields) != 'array' ? [$fields] : $fields;

        return [
            self::buildQueryFunction($value, $fields, $isFull)
        ];
    }


    private static function buildQueryFunction(Carbon|string $value, array $fields, bool $isFull): callable
    {
        return function (Builder $query) use ($value, $fields, $isFull) {
            $data = !($value instanceof Carbon) ? Carbon::parse($value) : $value;
            foreach ($fields as $key => $field) {
                BuilderQuery::applyQuery($query, $field, 'date', $data->toDateString(), $isFull, $key);
            }
        };
    }
}