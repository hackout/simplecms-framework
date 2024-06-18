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
        $selectList = collect($select ? $select : $model->getFillable());
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
        return $builder;
    }
}