<?php
namespace SimpleCMS\Framework\Services\Work;

use function is_array;
use function is_numeric;

/**
 * 请求更新数据转换
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 */
class ConvertData
{

    public static function run(mixed $model, array $data, array $mediaFields = []): array
    {
        $sql = $data;
        $files = [];
        $multiple = [];
        $mediaFields = self::parseMediaFields($mediaFields);
        if (app(HasMedia::class)->run($model)) {
            $sql = [];
            foreach ($data as $field => $value) {
                $result = self::makeResult($value, $field, $mediaFields);
                $result['type'] == 'sql' && $sql[$field] = $value;
                $result['type'] == 'multiple' && $multiple[$field] = $value;
                $result['type'] == 'files' && $files[$field] = $value;
            }
        }
        return [$sql, $files, $multiple];
    }

    private static function parseMediaFields(array $mediaFields): array
    {
        if (empty($mediaFields))
            return $mediaFields;
        $result = [];
        foreach ($mediaFields as $key => $field) {
            if (is_numeric($key)) {
                $result[$field] = $field;
            } else {
                $result[$key] = $field;
            }
        }
        return $result;
    }

    private static function makeResult($values, string $field, array $mediaFields): array
    {
        $result = [
            'field' => $field,
            'value' => $values,
            'type' => 'sql'
        ];
        if (isset($mediaFields[$field]) && !empty($values)) {
            $result['value'] = $values;
            $result['type'] = is_array($values) ? 'multiple' : 'files';
            return $result;
        }
        return $result;
    }

}