<?php

namespace SimpleCMS\Framework\Services;

/**
 * @use BaseService
 * @use BuilderInterface
 */
trait RetrieveServiceTrait
{
    /**
     * @param mixed $columns
     * @return int
     */
    public function count($columns = '*'): int
    {
        $builder = $this->getBuilder();
        return $this->getCacheData([$builder->toRawSql(), 'count', $columns], fn() => $builder->count($columns));
    }

    /**
     * @param mixed $column
     * @return mixed
     */
    public function min($column)
    {
        $builder = $this->getBuilder();
        return $this->getCacheData([$builder->toRawSql(), 'min', $column], fn() => $builder->min($column));
    }

    /**
     * @param mixed $column
     * @return mixed
     */
    public function max($column)
    {
        $builder = $this->getBuilder();
        return $this->getCacheData([$builder->toRawSql(), 'max', $column], fn() => $builder->max($column));
    }

    /**
     * @param mixed $column
     * @return mixed
     */
    public function sum($column)
    {
        $builder = $this->getBuilder();
        return $this->getCacheData([$builder->toRawSql(), 'sum', $column], fn() => $builder->sum($column) ?: 0);

    }

    /**
     * @param mixed $column
     * @return mixed
     */
    public function avg($column)
    {
        $builder = $this->getBuilder();

        return $this->getCacheData([$builder->toRawSql(), 'avg', $column], fn() => $builder->avg($column));
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
