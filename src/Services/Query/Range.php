<?php
namespace SimpleCMS\Framework\Services\Query;

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
     * @param  array|string       $value
     * @param  array|string       $fields
     * @param  bool               $isFull 
     * @return array|bool
     */
    public static function builder(array|string $value, array|string $fields, bool $isFull = false): array|bool
    {
        if (!is_array($value))
            $value = [trim($value), null];
        if (!is_array($fields))
            $fields = [$fields];
        $values = array_slice($value, 0, 2);
        if(head($values) === null && last($values) === null)
        {
            return false;
        }
        return [
            function (Builder $query) use ($values, $fields, $isFull) {
                $method = 'whereAny';
                if ($isFull)
                    $method = 'whereAll';
                $condition = 'between';
                $data = $values;
                if(head($values) === null)
                {
                    $condition = '<';
                    $data = last($values);
                }
                if(last($values) === null)
                {
                    $condition = '>=';
                    $data = head($values);
                }
                $query->$method($fields, $condition,$data);
            }
        ];
    }
}