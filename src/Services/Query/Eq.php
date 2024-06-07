<?php
namespace SimpleCMS\Framework\Services\Query;

use Illuminate\Database\Query\Builder;

class Eq
{
    /**
     * eq筛选查询
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
        $value = is_array($value) ? head($value) : $value;
        if (!is_array($fields))
            $fields = [$fields];
        return [
            function (Builder $query) use ($value, $fields, $isFull) {
                $method = 'whereAny';
                if ($isFull)
                    $method = 'whereAll';
                $query->$method($fields, '=', $value);
            }
        ];
    }
}