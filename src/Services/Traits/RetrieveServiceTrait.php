<?php

namespace SimpleCMS\Framework\Services\Traits;

/**
 * @use \SimpleCMS\Framework\Services\BaseService
 * @use \SimpleCMS\Framework\Services\SimpleService
 */
trait RetrieveServiceTrait
{
    /**
     * @param mixed $columns
     * @return int
     */
    public function count($columns = '*'): int
    {
        $builder = $this->/** @scrutinizer ignore-call */getBuilder();
        return $this->/** @scrutinizer ignore-call */getCacheData([$builder->toRawSql(), 'count', $columns], fn() => $builder->count($columns));
    }

    /**
     * @param mixed $column
     * @return mixed
     */
    public function min($column)
    {
        $builder = $this->/** @scrutinizer ignore-call */getBuilder();
        return $this->/** @scrutinizer ignore-call */getCacheData([$builder->toRawSql(), 'min', $column], fn() => $builder->min($column));
    }

    /**
     * @param mixed $column
     * @return mixed
     */
    public function max($column)
    {
        $builder = $this->/** @scrutinizer ignore-call */getBuilder();
        return $this->/** @scrutinizer ignore-call */getCacheData([$builder->toRawSql(), 'max', $column], fn() => $builder->max($column));
    }

    /**
     * @param mixed $column
     * @return mixed
     */
    public function sum($column)
    {
        $builder = $this->/** @scrutinizer ignore-call */getBuilder();
        return $this->/** @scrutinizer ignore-call */getCacheData([$builder->toRawSql(), 'sum', $column], fn() => $builder->sum($column) ?: 0);

    }

    /**
     * @param mixed $column
     * @return mixed
     */
    public function avg($column)
    {
        $builder = $this->/** @scrutinizer ignore-call */getBuilder();

        return $this->/** @scrutinizer ignore-call */getCacheData([$builder->toRawSql(), 'avg', $column], fn() => $builder->avg($column));
    }

    /**
     * @param mixed $column
     * @return mixed
     */
    public function average($column)
    {
        return $this->avg($column);
    }
}
