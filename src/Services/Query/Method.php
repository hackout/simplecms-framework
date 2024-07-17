<?php
namespace SimpleCMS\Framework\Services\Query;

class Method
{
    public static function method(string $condition = '=', bool $isFull, int $key = 0):string
    {
        if($key == 0)
        {
            return static::getMethod($condition);
        }
        return $isFull ? static::getMethod($condition) : static::getOrMethod($condition);
    }

    private static function getMethod($condition):string
    {
        return match($condition){
            'between' => 'whereBetween',
            'not_between' => 'whereNotBetween',
            'in' => 'whereIn',
            'not_in' => 'whereNotIn',
            'null' => 'whereNull',
            'not_null' => 'whereNotNull',
            'date' => 'whereDate',
            'month' => 'whereMonth',
            'day' => 'whereDay',
            'year' => 'whereYear',
            'time' => 'whereTime',
            'raw' => 'whereRaw',
            default => 'where'
        };
    }

    private static function getOrMethod($condition):string
    {
        return match($condition){
            'between' => 'orWhereBetween',
            'not_between' => 'orWhereNotBetween',
            'in' => 'orWhereIn',
            'not_in' => 'orWhereNotIn',
            'null' => 'orWhereNull',
            'not_null' => 'orWhereNotNull',
            'date' => 'orWhereDate',
            'month' => 'orWhereMonth',
            'day' => 'orWhereDay',
            'year' => 'orWhereYear',
            'time' => 'orWhereTime',
            'raw' => 'orWhereRaw',
            default => 'orWhere'
        };
    }
}