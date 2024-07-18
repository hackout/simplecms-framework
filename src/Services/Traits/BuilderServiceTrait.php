<?php

namespace SimpleCMS\Framework\Services\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use SimpleCMS\Framework\Services\Work\MakeSelect;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

/**
 * @use \SimpleCMS\Framework\Services\BaseService
 * @use \SimpleCMS\Framework\Services\SimpleService
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
        if ($query = $this->/** @scrutinizer ignore-call */ getQuery()) {
            optional($this->builder)->where($query);
        }
    }

    private function builderWith(): void
    {
        if ($with = $this->/** @scrutinizer ignore-call */ getWith()) {
            optional($this->builder)->with($with);
        }
    }

    private function builderGroup(): void
    {
        if ($group = $this->/** @scrutinizer ignore-call */ getGroup()) {
            optional($this->builder)->groupBy($group);
        }
    }
    private function builderHas(): void
    {
        if ($has = $this->/** @scrutinizer ignore-call */ getHas()) {
            foreach ($has as $key => $value) {
                optional($this->builder)->whereHas($key, function ($query) use ($value) {
                    $query->where($value);
                });
            }
        }
    }

    private function builderSelect(): void
    {
        if ($select = $this->/** @scrutinizer ignore-call */ getSelect()) {
            optional($this->builder)->selectRaw(Arr::join($select, ','));
        }
    }

    private function builder(?string $prop = null, ?string $order = null): Builder|Model|null
    {
        $this->builder = $this->/** @scrutinizer ignore-call */ getModel();
        if (empty($this->builder))
            return null;

        $this->builderQuery();
        $this->builderWith();
        $this->builderGroup();
        $this->builderHas();
        $this->/** @scrutinizer ignore-call */ setSelect((new MakeSelect)->run($this->/** @scrutinizer ignore-call */ getModel(), $this->/** @scrutinizer ignore-call */ getSelect()));
        $this->builderSelect();
        if ($prop) {
            $this->/** @scrutinizer ignore-call */ setOrderKey($prop);
        }
        if ($order) {
            $this->/** @scrutinizer ignore-call */ setOrderType(Str::remove('ending', $order));
        }
        $this->builder->orderBy($this->/** @scrutinizer ignore-call */ getOrderKey(), $this->/** @scrutinizer ignore-call */ getOrderType());

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
        return DB::table($tableName ?? $this->/** @scrutinizer ignore-call */ getTableName());
    }
}
