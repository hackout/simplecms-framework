<?php
namespace SimpleCMS\Framework\Services\Work;

use Illuminate\Support\Arr;

/**
 * 绑定Select
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 */
class MakeSelect
{
    public function run(mixed $model, mixed $builder, $select = []): mixed
    {
        if ($select) {
            $builder = $builder->select($select);
        } else {
            $selectList = collect(array_keys($model->getCasts()))->concat(collect($model->getFillable()))->unique();
            $casts = $model->getCasts();
            $selectArr = [];
            $selectList->each(function ($field) use ($casts, &$selectArr) {
                if (array_key_exists($field, $casts)) {
                    if (class_exists($casts[$field]) && strpos($casts[$field], 'Casts') !== false) {
                        $selectArr[] = (new $casts[$field])->select($field);
                    } else {
                        $selectArr[] = $field;
                    }
                } else {
                    $selectArr[] = $field;
                }
            });
            $builder = $builder->selectRaw(Arr::join($selectArr, ','));
        }
        return $builder;
    }
}