<?php
namespace SimpleCMS\Framework\Services\Query;

use Illuminate\Database\Eloquent\Builder;

class Search
{
    /**
     * 搜索字段
     * 
     * 说明:
     * 
     * $isFull 如果为真则需要所有字段均出现该关键词
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array|string       $keyword
     * @param  array|string       $fields
     * @param  bool               $isFull 
     * @return array
     */
    public static function builder(array|string $keyword, array|string $fields,bool $isFull = false): array
    {
        $keywords = !is_array($keyword) ? explode(',',trim($keyword)) : $keyword;
        if(!is_array($fields)) $fields = [$fields];
        return [
            function(Builder $query) use($keywords,$fields,$isFull){
                foreach($keywords as $index => $keyword)
                {
                    $method = 'whereAny';
                    if($isFull) $method = 'whereAll';
                    if($index)
                    {
                        $method = 'orWhereAny';
                        if($isFull) $method = 'orWhereAll';
                    }
                    $query->$method($fields,'LIKE',"%$keyword%");
                }
            }
        ];
    }
}