<?php
namespace SimpleCMS\Framework\Services\Query;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class OrderBy
{
    /**
     * 重装OrderBy
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  Builder     $builder
     * @param  string|null $column
     * @param  string|null $direction
     * @param  string|null $defaultColumn
     * @param  string|null $defaultDirection
     * @return Builder
     */
    public static function builder(Builder $builder, string $column = null, string $direction = null, string $defaultColumn = 'created_at', string $defaultDirection = 'desc'): Builder
    {
        Str::remove('ending', $direction);
        if (!$column && !$direction && !$defaultColumn && !$defaultDirection)
            return $builder;
        $direction = $direction ?: $defaultDirection;
        $column = $column ?: $defaultColumn;
        return $builder->orderBy($column, $direction);
    }
}