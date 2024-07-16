<?php
namespace SimpleCMS\Framework\Services\Set;

use function is_array;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Relations\Relation;

class Has
{
    /**
     * @param ?array<string,callable> $data
     * @param null|array<string,callable> $value
     * @return array<string,callable>
     */
    public static function run(array $data = [], null|array|Relation|string $value = null): array
    {
        if (!$value || Arr::isList($value))
            return [];
        if(is_array($value))
        {
            foreach ($value as $key => $val) {
                $data[$key] = $val;
            }
        }else{
            $data[] = $value;
        }
        return $data;
    }

}