<?php
namespace SimpleCMS\Framework\Services\Query;

use Carbon\Carbon;
use function is_array;
use function array_pad;
use Illuminate\Database\Query\Builder;

class Year
{
    /**
     * 年筛选查询
     * 
     * 说明:
     * 
     * $isFull 如果为真则需要所有字段均出现该关键词
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  int|float|string   $value
     * @param  array|string       $fields
     * @param  bool               $isFull 
     * @return array
     */
    public static function builder(int|float|string $value, array|string $fields, bool $isFull = false): array
    {
        $fields = gettype($fields) != 'array' ? [$fields] : $fields;

        return [
            self::buildQueryFunction($value, $fields, $isFull)
        ];
    }

    private static function buildQueryFunction(int|float|string $value, array $fields, bool $isFull): callable
    {
        return function (Builder $query) use ($value, $fields, $isFull) {
            $data = self::parseData($value);
            foreach ($fields as $key => $field) {
                BuilderQuery::applyQuery($query, $field, 'year', $data, $isFull, $key);
            }
        };
    }

    private static function parseData(int|float|string $value): string
    {
        $value = (int) $value;
        $value = $value > 3000 ? 3000 : $value;
        return substr((string) ($value + 10000), 1, 4);
    }
}