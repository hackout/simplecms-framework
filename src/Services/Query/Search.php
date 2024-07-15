<?php
namespace SimpleCMS\Framework\Services\Query;

use function explode;
use function is_array;
use function array_pad;
use Illuminate\Database\Query\Builder;

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
     * @param  array|string       $keywords
     * @param  array|string       $fields
     * @param  bool               $isFull 
     * @return array
     */
    public static function builder(...$params): array
    {
        list($keywords, $fields, $isFull) = array_pad($params, 3, null);
        if (!is_array($keywords)) {
            $keywords = explode(',', trim($keywords));
        }
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        $isFull = !empty($isFull);
        return [
            function (Builder $query) use ($keywords, $fields, $isFull) {
                foreach ($keywords as $index => $keyword) {
                    $method = 'whereAny';
                    if ($isFull)
                        $method = 'whereAll';
                    if ($index) {
                        $method = 'orWhereAny';
                        if ($isFull)
                            $method = 'orWhereAll';
                    }
                    $query->$method($fields, 'LIKE', "%$keyword%");
                }
            }
        ];
    }
}