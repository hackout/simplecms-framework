<?php
namespace SimpleCMS\Framework\Services\Work;

use function is_string;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use SimpleCMS\Framework\Contracts\SimpleMedia;
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
     * @param  UploadedFile|string $file
     * @param  string              $columnName
     * @return void
     */
    public static function run(mixed $model, UploadedFile|string $file, string $columnName): void
    {
        if($model instanceof SimpleMedia)
        if ($model->getHasOneMedia() && in_array($columnName, $model->getHasOneMedia())) {
            $model->getMedia($columnName)->each(fn(Media $media) => $media->delete());
        }

        if ($file instanceof UploadedFile) {
            $model->addMedia($file)->toMediaCollection($columnName);
        }
        if (is_string($file)) {
            if (strpos($file, '/') === 0) {
                if (strpos($file, '/storage') === 0) {
                    if (file_exists(storage_path(str_replace('/storage', '/app/public', $file)))) {
                        $model->addMedia(storage_path(str_replace('/storage', '/app/public', $file)))->toMediaCollection($columnName);
                    }
                } else {
                    if (file_exists(public_path($file))) {
                        $model->addMedia(public_path($file))->toMediaCollection($columnName);
                    }
                }
            } elseif (Str::isUrl($file)) {
                $model->addMediaFromUrl($file)->toMediaCollection($columnName);
            } elseif (is_base_image($file)) {
                $model->addMediaFromBase64($file)->toMediaCollection($columnName);
            }
        }
    }
}