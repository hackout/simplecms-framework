<?php
namespace SimpleCMS\Framework\Services\Query;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

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
     * @param  array|string       $value
     * @param  array|string       $fields
     * @param  bool               $isFull 
     * @return array
     */
    public static function builder(array|string $value, array|string $fields, bool $isFull = false): array
    {
        $values = !is_array($value) ? [trim($value)] : $value;
        if (!is_array($fields))
            $fields = [$fields];
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
                    $data = !($value instanceof Carbon) ? Carbon::parse($value) : $value;
                    $query->$method($fields, 'Date', $data->toDateString());
                }
            }
        ];
    }
}