<?php
namespace SimpleCMS\Framework\Services\Query;

use Illuminate\Database\Query\Builder;

class Like
{
    /**
     * like筛选查询
     * 
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array|string       $value
     * @param  array|string       $fields
     * @return array
     */
    public static function builder(...$params): array
    {
        list($value, $fields) = array_pad($params, 2, null);
        $value = trim($value);
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        return [
            function (Builder $query) use ($value, $fields) {
                $method = 'whereAny';
                $query->$method($fields, 'LIKE', $value);
            }
        ];
    }
}