<?php
namespace SimpleCMS\Framework\Services\Work;

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
    public function run(mixed $model, $select = []): mixed
    {
        $selectList = new Collection($select ? $select : $model->getFillable());
        $casts = $model->getCasts();
        $primaryKey = $model->getKeyName();
        $defaultSelect = [
            'deleted_at',
            'created_at',
            'updated_at'
        ];
        if (!$select) {
            if ($primaryKey && !in_array($primaryKey, $selectList->toArray())) {
                $selectList->push($primaryKey);
            }
            foreach ($defaultSelect as $defaultField) {
                if (array_key_exists($defaultField, $casts)) {
                    $selectList->push($defaultField);
                }
            }
        }
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
        return $selectArr;
    }
}