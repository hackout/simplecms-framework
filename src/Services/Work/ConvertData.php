<?php
namespace SimpleCMS\Framework\Services\Work;

use function is_array;

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
        if (app(HasMedia::class)->run($model)) {
            $sql = [];
            foreach ($data as $field => $value) {
                $result = self::makeResult($value, $field, $mediaFields);
                $result['type'] == 'sql' && $sql[$field] = $value;
                $result['type'] == 'multiple' && $multiple[$field] = $value;
                $result['type'] == 'file' && $files[$field] = $value;
            }
        }
        return [$sql, $files, $multiple];
    }

    private static function makeResult($values, string $field, array $mediaFields): array
    {
        $result = [
            'field' => $field,
            'value' => $values,
            'type' => 'sql'
        ];
        if (in_array($field, $mediaFields) && !empty($values)) {
            $result['value'] = $values;
            $result['type'] = is_array($values) ? 'multiple' : 'file';
            return $result;
        }
        return $result;
    }

}