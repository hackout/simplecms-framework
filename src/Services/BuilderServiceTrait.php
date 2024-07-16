<?php

namespace SimpleCMS\Framework\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

/**
 * @use BaseService
 * @abstract BaseService
 */
trait BuilderServiceTrait
{
    private Builder|Model|null $builder;

    /**
     * 获取Builder
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param ?string $prop
     * @param ?string $order
     * @return Builder|Model|null
     */
    public function getBuilder(?string $prop = null, ?string $order = null)
    {
        return $this->builder($prop, $order);
    }

    private function builderQuery(): void
    {
        if ($query = $this->getQuery()) {
            $this->builder->where($query);
        }
    }

    private function builderWith(): void
    {
        if ($with = $this->getWith()) {
            $this->builder->with($with);
        }
    }

    private function builderGroup(): void
    {
        if ($group = $this->getGroup()) {
            $this->builder->groupBy($group);
        }
    }
    private function builderHas(): void
    {
        if ($has = $this->getHas()) {
            foreach ($has as $key => $value) {
                $this->builder->whereHas($key, function ($query) use ($value) {
                    $query->where($value);
                });
            }
        }
    }

    private function builderSelect(): void
    {
        if ($select = $this->getSelect()) {
            $this->builder->selectRaw(Arr::join($select, ','));
        }
    }

    private function builder(?string $prop = null, ?string $order = null): Builder|Model|null
    {
        $this->builder = $this->getModel();
        if (!$this->builder)
            return null;

        $this->builderQuery();
        $this->builderWith();
        $this->builderGroup();
        $this->builderHas();
        $this->setSelect((new Work\MakeSelect)->run($this->getModel(), $this->getSelect()));
        $this->builderSelect();
        if ($prop) {
            $this->setOrderKey($prop);
        }
        if ($order) {
            $this->setOrderType(Str::remove('ending', $order));
        }
        $this->builder->orderBy($this->getOrderKey(), $this->getOrderType());

        return $this->builder;
    }


    /**
     * DB请求
     * 
     * @param ?string $tableName
     * @return DatabaseBuilder
     */
    public function db(string $tableName = null): DatabaseBuilder
    {
        return DB::table($tableName ?? $this->getTableName());
    }
}
