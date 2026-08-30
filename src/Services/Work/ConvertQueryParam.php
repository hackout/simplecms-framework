<?php
namespace SimpleCMS\Framework\Services\Work;


/**
 * 转换listQuery参数
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 */
class ConvertQueryParam
{
    public static function run(string|array $value, $key): array
    {
        $values = (array) $value;
        list($action, $fields, $extra) = array_pad($values, 3, null);
        !$fields && $fields = $key;
        $queryModel = self::matchQuery($action);
        return [
            $queryModel,
            $fields,
            (bool) $extra
        ];
    }

    private static function matchQuery($action): string
    {
        return match ($action) {
            'search' => \SimpleCMS\Framework\Services\Query\Search::class,
            'datetime_range' => \SimpleCMS\Framework\Services\Query\DateTimeRange::class,
            'range' => \SimpleCMS\Framework\Services\Query\Range::class,
            'datetime' => \SimpleCMS\Framework\Services\Query\DateTime::class,
            'date' => \SimpleCMS\Framework\Services\Query\Date::class,
            'year' => \SimpleCMS\Framework\Services\Query\Year::class,
            'month' => \SimpleCMS\Framework\Services\Query\Month::class,
            'day' => \SimpleCMS\Framework\Services\Query\Day::class,
            'in' => \SimpleCMS\Framework\Services\Query\In::class,
            'like' => \SimpleCMS\Framework\Services\Query\Like::class,
            default => \SimpleCMS\Framework\Services\Query\Eq::class,
        };
    }
}