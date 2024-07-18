<?php
namespace SimpleCMS\Framework\Services\Work;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

/**
 * 转换模型到数组
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 * @template TKey of array-key
 *
 * @template-covariant TValue
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
                list($key,$field) = self::parseField($value);

                $newItem->put($field,self::processData($item,$key));
            }
            return $newItem;
        });
    }

    /**
     * @param mixed $item
     * @param string $key
     * @return TValue|null
     */
    private static function processData($item,string $key)
    {
        $dotKey = Str::before($key,':');
        $result = object_get($item,$dotKey);
        if (strpos($key, ':') !== false) {
            $only = explode(',',Str::afterLast($key,':'));
            $result = self::parseOnlyValue($result,$only);
        }
        return object_get($item,$key);
    }

    /**
     * @param mixed $item
     * @param string $key
     * @return TValue|null
     */
    private static function parseOnlyValue($result,array $only)
    {
        list($fields,$alias) = self::parseAliasField($only);
        if(gettype($result) == 'array')
        {
            return self::convertArrOnly($result,$fields,$alias);
        }
        return self::convertCollectionOnly($result,$fields,$alias);
    }

    /**
     * 
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  \Illuminate\Database\Eloquent\Model         $result
     * @param  array          $fields
     * @param  array          $alias
     * @return TValue|null
     */
    private static function convertCollectionOnly($result,array $fields,array $alias)
    {
        foreach($alias as $key=>$field)
        {
            $result->$field = $result->{$fields[$key]};
        }
        return  $result->only($alias);
    }

    private static function convertArrOnly(array $result,array $fields,array $alias):array
    {
        if(Arr::isList($result)) return $result;
        $result = Arr::only($result,$fields);
        $newResult = [];
        foreach($alias as $key=>$name)
        {
            $newResult[$name] = isset($result[$key]) ? $result[$key] : null;
        }
        return $newResult;
    }

    private static function parseAliasField(array $only):array
    {
        $fields = [];
        $alias = [];
        foreach($only as $rs)
        {
            $fields[] = Str::beforeLast($rs,' as ');
            $alias[] = Str::afterLast($rs,' as ');
        }
        return [$fields,$alias];
    }

    /**
     * @param string $value
     * @return array<string,TKey>
     */
    private static function parseField(string $value): array
    {
        return array_pad(explode(' as ', strtolower($value)),2,null);
        if(empty($field))
        {
            $field = self::parseFieldData($key);
        }
        return [$key,$field];
    }


    private static function parseFieldDataByList(string $key):array
    {
        $keys = [];
        foreach(explode(',',Str::afterLast($key,':')) as $rs)
        {
            $keys[] = Str::afterLast($rs,' as ');
        }
        return $keys;
    }

}