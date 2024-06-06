<?php
namespace SimpleCMS\Framework\Services\Query;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class In
{
    /**
     * IN筛选查询
     * 
     * 说明:
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array|string       $value
     * @param  array|string       $fields
     * @return array
     */
    public static function builder(array|string $value, array|string $fields): array
    {
        $values = !is_array($value) ? [trim($value)] : $value;
        if (!is_array($fields))
            $fields = [$fields];
        return [
            function (Builder $query) use ($values, $fields) {
                foreach($fields as $index => $field)
                {
                    $method = 'whereIn';
                    if($index)
                    {
                        $method = 'orWhereIn';
                    }
                    $query->$method($field,$values);
                }
            }
        ];
    }
}