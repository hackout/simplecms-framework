<?php
namespace SimpleCMS\Framework\Services\Work;

use Illuminate\Database\Eloquent\Collection;
use SimpleCMS\Framework\Exceptions\SimpleException;
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
    public static function run(Collection|BaseCollection $data, $fieldList): Collection|BaseCollection
    {
        return $data->map(function ($item) use ($fieldList) {
            if (!$fieldList)
                return $item;
            $newItem = collect();
            foreach ($fieldList as $value) {
                list($key, $val) = array_pad(explode(' as ', strtolower($value)), 2, null);
                list($relation, $key1) = array_pad(explode('.', strtolower($key)), 2, null);
                $key1 = $key1 ?: $relation;
                $val = $val ?: $key1;
                if ($relation === $key) {
                    if (strpos($relation, ':') === false) {
                        $newItem->put($val, $item->{$key});
                    } else {
                        list($relation, $relationColumnString) = explode(':', $relation);
                        list($val, $valColumnString) = array_pad(explode(':', $val), 2, null);
                        if (!$relationColumnString) {
                            throw new SimpleException(trans('simplecms:range_valid'));
                        }
                        $valColumns = $valColumnString ? explode(',', $valColumnString) : null;
                        $relationColumns = explode(',', $relationColumnString);
                        $newItem->put($val, optional($item->{$relation})->map(function ($item) use ($relationColumns, $valColumns) {
                            $newItem = [];
                            foreach ($relationColumns as $index => $key) {
                                $newItem[array_key_exists($index, $valColumns) ? $valColumns[$index] : $key] = $item->{$key};
                            }
                            return $newItem;
                        }));
                    }
                } else {
                    if (strpos($relation, ':') === false) {
                        $newItem->put($val, optional($item->{$relation})->{$key1});
                    } else {
                        list($relation, $relationColumnString) = explode(':', $relation);
                        list($val, $valColumnString) = array_pad(explode(':', $val), 2, null);
                        if (!$relationColumnString) {
                            throw new SimpleException(trans('simplecms:range_valid'));
                        }
                        $valColumns = $valColumnString ? explode(',', $valColumnString) : null;
                        $relationColumns = explode(',', $relationColumnString);
                        $newItem->put($val, optional($item->{$relation})->map(function ($item) use ($relationColumns, $valColumns) {
                            $newItem = [];
                            foreach ($relationColumns as $index => $key) {
                                $newItem[array_key_exists($index, $valColumns) ? $valColumns[$index] : $key] = $item->{$key};
                            }
                            return $newItem;
                        }));
                    }
                }
            }
            return $newItem;
        });
    }
}