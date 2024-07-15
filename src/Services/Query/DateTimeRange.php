<?php
namespace SimpleCMS\Framework\Services\Query;

use Carbon\Carbon;
use function is_array;
use function array_pad;
use function is_string;
use function array_slice;
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
     * @return array
     */
    public static function builder(...$params): array
    {
        list($value, $fields, $isFull) = array_pad($params, 3, null);
        if (!is_array($value)) {
            $value = [trim($value), null];
        }
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        $isFull = !empty($isFull);

        $values = array_slice($value, 0, 2);
        if (head($values) === null && last($values) === null) {
            return [];
        }
        return [
            function (Builder $query) use ($values, $fields, $isFull) {
                $condition = 'between';
                $data = $values;
                if (head($values) === null) {
                    $condition = '<';
                    $data = is_string(last($values)) ? Carbon::parse(last($values)) : last($values);
                }
                if (last($values) === null) {
                    $condition = '>=';
                    $data = is_string(head($values)) ? Carbon::parse(head($values)) : head($values);
                }
                if (is_array($data)) {
                    if (!(head($data) instanceof Carbon)) {
                        $data[0] = Carbon::parse(head($data));
                    }
                    if (!(last($data) instanceof Carbon)) {
                        $data[1] = Carbon::parse(last($data));
                    }
                }
                foreach ($fields as $key => $field) {
                    if ($condition != 'between') {
                        if ($isFull) {
                            $query->where($field, $condition, $data);
                        } else {
                            if ($key) {
                                $query->orWhere($field, $condition, $data);
                            } else {
                                $query->where($field, $condition, $data);
                            }
                        }
                    } else {
                        if ($isFull) {
                            $query->whereBetween($field, $data);
                        } else {
                            if ($key) {
                                $query->orWhereBetween($field, $data);
                            } else {
                                $query->whereBetween($field, $data);
                            }
                        }
                    }
                }
            }
        ];
    }
}