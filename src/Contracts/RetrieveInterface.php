<?php

namespace SimpleCMS\Framework\Contracts;

interface RetrieveInterface
{

    /**
     * Retrieve the "count" result of the query.
     *
     * @param  \Illuminate\Contracts\Database\Query\Expression|string  $columns
     * @return int
     */
    public function count($columns = '*'): int;

    /**
     * Retrieve the minimum value of a given column.
     *
     * @param  \Illuminate\Contracts\Database\Query\Expression|string  $column
     * @return mixed
     */
    public function min($column);

    /**
     * Retrieve the maximum value of a given column.
     *
     * @param  \Illuminate\Contracts\Database\Query\Expression|string  $column
     * @return mixed
     */
    public function max($column);
    
    /**
     * Retrieve the sum of the values of a given column.
     *
     * @param  \Illuminate\Contracts\Database\Query\Expression|string  $column
     * @return mixed
     */
    public function sum($column);

    /**
     * Retrieve the average of the values of a given column.
     *
     * @param  \Illuminate\Contracts\Database\Query\Expression|string  $column
     * @return mixed
     */
    public function avg($column);

    /**
     * Alias for the "avg" method.
     *
     * @param  \Illuminate\Contracts\Database\Query\Expression|string  $column
     * @return mixed
     */
    public function average($column);
}
