<?php
namespace SimpleCMS\Framework\Services\Work;

use function is_array;
use function is_string;
use Illuminate\Http\UploadedFile;

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
        $multipleFiles = [];
        if (app(HasMedia::class)->run($model)) {
            $sql = [];
            foreach ($data as $field => $value) {
                if (!empty($value) && ($value instanceof UploadedFile || (is_string($field) && isset($mediaFields[$field])))) {
                    $files[$field] = $value;
                } elseif (is_array($value) && !empty($value) && (head($value) instanceof UploadedFile || isset($mediaFields[$field]))) {
                    $multipleFiles[$field] = $value;
                } else {
                    $sql[$field] = $value;
                }
            }
        }
        return [$sql, $files, $multipleFiles];
    }
}