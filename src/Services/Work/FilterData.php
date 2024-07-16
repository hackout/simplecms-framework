<?php
namespace SimpleCMS\Framework\Services\Work;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

/**
 * 转换模型到数组
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 */
class FilterData
{
    /** 
     * @param \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection $data 
     * @param mixed $fieldList 
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection 
     */
    public static function run(Collection|BaseCollection $data, $fieldList = null): Collection|BaseCollection
    {
        return $data->map(function ($item) use ($fieldList) {
            if (!$fieldList) {
                return $item;
            }

            $newItem = new BaseCollection();

            foreach ($fieldList as $value) {
                list($key, $val, $relation, $key1) = self::parseField($value);

                if (strpos($relation, ':') === false) {
                    $result = self::processRelationByNotSplit($item, $relation, $key1, $key, $val);
                } else {
                    $result = self::processRelationBySplit($item, $relation, $val);
                }
                $newItem->put($result['key'], $result['value']);
            }
            return $newItem;
        });
    }

    private static function parseField($value): array
    {
        list($key, $val) = array_pad(explode(' as ', strtolower($value)), 2, null);
        list($relation, $key1) = array_pad(explode('.', strtolower($key)), 2, null);
        $key1 = $key1 ?: $relation;
        $val = $val ?: $key1;
        return [$key, $val, $relation, $key1];
    }

    private static function parseRelationColumns($relation, $val): array
    {
        list($relation, $relationColumnString) = explode(':', $relation);
        list($val, $valColumnString) = array_pad(explode(':', $val), 2, null);
        $valColumns = $valColumnString ? explode(',', $valColumnString) : null;
        $relationColumns = explode(',', $relationColumnString);
        return [
            $relation,
            $val,
            $valColumns,
            $relationColumns
        ];
    }

    private static function processRelationBySplit($item, $relation, $val): array
    {
        $result = [
            'key' => $val,
            'value' => null
        ];
        list($relation, $val, $valColumns, $relationColumns) = self::parseRelationColumns($relation, $val);
        $result['key'] = $val;
        $result['value'] = optional($item->$relation)->map(function ($_item) use ($relationColumns, $valColumns) {
            $newItem = [];
            foreach ($relationColumns as $index => $key) {
                $column = isset($valColumns[$index]) ? $valColumns[$index] : $key;
                $newItem[$column] = $_item->$key;
            }
            return $newItem;
        })->toArray();
        return $result;
    }

    private static function processRelationByNotSplit($item, $relation, $key1, $key, $val): array
    {
        $result = [
            'key' => $val,
            'value' => $relation === $key ? $item->$key : optional($item->$relation)->$key1
        ];
        return $result;
    }

}