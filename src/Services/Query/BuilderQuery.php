<?php
namespace SimpleCMS\Framework\Services\Query;

use Illuminate\Database\Query\Builder;

class BuilderQuery
{
    /**
     * 提交query
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $field
     * @param string $condition
     * @param mixed $data
     * @param bool $isFull
     * @param int $key
     * @return void
     */
    public static function applyQuery(Builder $query, string $field, string $condition, mixed $data, bool $isFull, int $key): void
    {
        $method = Method::method($condition, $isFull, $key);
        $paramNumber = Condition::paramNumber($condition);
        if ($paramNumber == 1) {
            $query->{$method}($data);
        } elseif ($paramNumber == 2) {
            $query->{$method}($field, $data);
        } else {
            $query->{$method}($field, $condition, $data);
        }
    }
}