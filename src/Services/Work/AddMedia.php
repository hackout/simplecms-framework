<?php
namespace SimpleCMS\Framework\Services\Work;

use function is_string;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * 上传附件类
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 */
class AddMedia
{
    /**
     * @param mixed $model
     * @param \Illuminate\Http\UploadedFile|string $file
     * @param string $columnName
     * @return void
     */
    public static function run($model, UploadedFile|string $file, string $columnName): void
    {
        if ($model->getHasOneMedia() && in_array($columnName, $model->getHasOneMedia())) {
            $model->getMedia($columnName)->each(fn(Media $media) => $media->delete());
        }

        if ($file instanceof UploadedFile) {
            $model->addMedia($file)->toMediaCollection($columnName);
        }
        if (is_string($file)) {
            self::applyStringMedia($model, $file, $columnName);
        }
    }

    private static function applyStringMedia($model, string $file, string $columnName): void
    {
        if (strpos($file, '/') === 0) {
            self::applyLocalFile($model, $file, $columnName);
        } elseif (Str::isUrl($file)) {
            $model->/** @scrutinizer ignore-call */addMediaFromUrl($file)->toMediaCollection($columnName);
        } elseif (is_base_image($file)) {
            $model->/** @scrutinizer ignore-call */addMediaFromBase64($file)->toMediaCollection($columnName);
        }
    }

    private static function applyLocalFile($model, string $file, string $columnName): void
    {
        if (strpos($file, '/storage') === 0) {
            self::applyStorageFile($model, $file, $columnName);
        } else {
            self::applyPublicFile($model, $file, $columnName);
        }
    }

    private static function applyStorageFile($model, string $file, string $columnName): void
    {
        if (file_exists(storage_path(str_replace('/storage', '/app/public', $file)))) {
            $model->addMedia(storage_path(str_replace('/storage', '/app/public', $file)))->toMediaCollection($columnName);
        }
    }
    private static function applyPublicFile($model, string $file, string $columnName): void
    {
        if (file_exists(public_path($file))) {
            $model->addMedia(public_path($file))->toMediaCollection($columnName);
        }
    }
}