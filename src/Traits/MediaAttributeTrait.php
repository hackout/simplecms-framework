<?php
namespace SimpleCMS\Framework\Traits;

use Spatie\MediaLibrary\Conversions\ImageGenerators;

use Exception;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

/**
 * 简易树型结构
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 *
 * 说明:
 *
 * 模块中必须存在parent_id父级ID
 * 模块class中引用:
 *
 *   use \SimpleCMS\Framework\Traits\MediaAttributeTrait;
 *
 * 模型方法:
 *
 *   $model->mediaToArray(); // 可以将附件转换为既定格式
 *
 */
trait MediaAttributeTrait
{
    public function mediaToArray(MediaCollection $medias): MediaCollection
    {
        return $medias->map(function (Media $item) {
            return [
                'name' => $item->file_name,
                'url' => url($item->getUrl()),
                'uuid' => $item->uuid,
                'poster' => $item->getUrl('shrinkage')
            ];
        });
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if (app(ImageGenerators\Image::class)->supportedMimeTypes()->contains($media->mime_type)) {
            $this->addMediaConversion('shrinkage')
                ->width(128)
                ->height(128);
        }
        if (app(ImageGenerators\Video::class)->supportedMimeTypes()->contains($media->mime_type)) {
            $this->addMediaConversion('shrinkage')
                ->width(128)
                ->height(128)
                ->extractVideoFrameAtSecond(1)
                ->performOnCollections('videos');
        }
        if (app(ImageGenerators\Pdf::class)->supportedMimeTypes()->contains($media->mime_type)) {
            $this->addMediaConversion('shrinkage')
                ->width(128)
                ->height(128)
                ->pdfPageNumber(1);
        }
    }
}