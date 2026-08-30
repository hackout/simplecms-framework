<?php
namespace SimpleCMS\Framework\Services\Work;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * 绑定Select
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 */
class MakeSelect
{
    /**
     * @param mixed $model
     * @param mixed $select
     * @return mixed
     */
    public function run(Model $model, $select = []): mixed
    {
        $selectList = new Collection(/** @scrutinizer ignore-type */ !empty($select) ? $select : (array) $model->getFillable());
        $casts = $model->getCasts();
        if (empty($select)) {
            $selectList = self::convertEmptySelect($selectList, $casts, $model);
        }
        $selectArr = [];
        $selectList->each(function ($field) use ($casts, &$selectArr) {
            $selectArr[] = self::getCastValue($casts, $field);
        });
        return $selectArr;
    }

    private static function getCastValue(array|null $casts, $field): string
    {
        if (empty($casts) || !array_key_exists($field, $casts)) {
            return $field;
        }
        if (class_exists($casts[$field]) && strpos($casts[$field], 'Casts') !== false) {
            return (new $casts[$field])->select($field);
        }
        return $field;
    }

    private static function convertDefaultSelect(Collection $collection, array|null $casts): Collection
    {
        $select = [
            'deleted_at',
            'created_at',
            'updated_at'
        ];

        foreach ($select as $field) {
            if (isset($casts[$field])) {
                $collection->push(/** @scrutinizer ignore-type */ $field);
            }
        }
        return $collection;
    }

    private static function convertEmptySelect(Collection $collection, array|null $casts, Model $model): Collection
    {
        if (!$primaryKey = $model->getKeyName()) {
            return $collection;
        }
        $collection->push(/** @scrutinizer ignore-type */ $primaryKey);
        return self::convertDefaultSelect($collection, $casts);
        ;
    }
}