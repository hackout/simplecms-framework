<?php
namespace SimpleCMS\Framework\Services\Query;

use Carbon\Carbon;
use function is_array;
use function array_pad;
use Illuminate\Database\Query\Builder;

class DateTime
{
    /**
     * 时间查询
     * 
     * 说明:
     * 
     * $isFull 如果为真则需要所有字段均出现该关键词
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array|string       $value
     * @param  array|string       $fields
     * @param  bool               $isFull 
     * @return array
     */
    public static function builder(...$params): array
    {
        list($value, $fields, $isFull) = array_pad($params, 3, null);
        $values = !is_array($value) ? [trim($value)] : $value;
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        $isFull = !empty($isFull);
        return [
            function (Builder $query) use ($values, $fields, $isFull) {
                foreach ($values as $index => $value) {
                    $method = 'whereAny';
                    if ($isFull)
                        $method = 'whereAll';
                    if ($index) {
                        $method = 'orWhereAny';
                        if ($isFull)
                            $method = 'orWhereAll';
                    }
                    $query->$method($fields, '>=', !($value instanceof Carbon) ? Carbon::parse($value) : $value);
                }
            }
        ];
    }
}