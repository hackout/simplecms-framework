<?php
namespace SimpleCMS\Framework\Services\Query;

use Carbon\Carbon;
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
     * @param  array|string       $value
     * @param  array|string       $fields
     * @param  bool               $isFull 
     * @return array|bool
     */
    public static function builder(array|string $value, array|string $fields, bool $isFull = false): array|bool
    {
        if (!is_array($value))
            $value = [trim($value), Carbon::now()->addYear()];
        if (!is_array($fields))
            $fields = [$fields];
        $values = array_slice($value, 0, 2);
        if (head($values) === null && last($values) === null) {
            return false;
        }
        return [
            function (Builder $query) use ($values, $fields, $isFull) {
                $data = $values;
                if (head($values) === null) {
                    $condition = '<';
                    $data = \is_string(last($values)) ? Carbon::parse(last($values)) : last($values);
                }
                if (last($values) === null) {
                    $condition = '>=';
                    $data = \is_string(head($values)) ? Carbon::parse(head($values)) : head($values);
                }
                if (is_array($data)) {
                    $condition = 'between';
                    if (!(head($data) instanceof Carbon)) {
                        $data[0] = Carbon::parse(head($data));
                    }
                    if (!(last($data) instanceof Carbon)) {
                        $data[1] = Carbon::parse(last($data));
                    }
                }
                foreach ($fields as $key => $field) {
                    if ($isFull) {
                        $query->where($field, $condition, $data);
                    } else {
                        if (!$key) {
                            $query->where($field, $condition, $data);
                        } else {
                            $query->orWhere($field, $condition, $data);
                        }
                    }
                }
            }
        ];
    }
}