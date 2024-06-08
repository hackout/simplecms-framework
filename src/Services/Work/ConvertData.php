<?php
namespace SimpleCMS\Framework\Services\Work;

use Illuminate\Http\UploadedFile;
use function is_string;
use function is_array;

/**
 * 请求更新数据转换
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 */
class ConvertData
{

    public function run(mixed $model,array $data, array $mediaFields = []): array
    {
        $sql = $data;
        $files = [];
        $multipleFiles = [];
        if (app(HasMedia::class)->run($model)) {
            $sql = [];
            foreach ($data as $field => $value) {
                if ($value && ($value instanceof UploadedFile || (is_string($field) && array_key_exists($field, $mediaFields)))) {
                    $files[$field] = $value;
                } elseif (is_array($value) && $value && (head($value) instanceof UploadedFile || array_key_exists($field, $mediaFields))) {
                    $multipleFiles[$field] = $value;
                } else {
                    $sql[$field] = $value;
                }
            }
        }
        return [$sql, $files, $multipleFiles];
    }
}