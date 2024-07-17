<?php
namespace SimpleCMS\Framework\Traits;


use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\Conversions\ImageGenerators;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * 扩展InteractsWithMedia
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 *
 * 说明:
 * 模块class中引用:
 *
 *   use \SimpleCMS\Framework\Traits\MediaAttributeTrait;
 * 
 * @use \Illuminate\Database\Eloquent\Model
 * @abstract \Illuminate\Database\Eloquent\Model
 *
 */
trait MediaAttributeTrait
{
    use InteractsWithMedia;
    /**
     * 获取既定规格附件
     *
     * 数组格式:
     *       name: 文件名
     *       url:  外部地址
     *       uuid: UUID
     *     poster: 缩率图
     * 
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string $collectionName
     * @param  array  $filters
     * @return array<string,string>
     */
    public function getMediaArray(string $collectionName = 'default', array|callable $filters = []): array
    {
        return $this->getMedia($collectionName, $filters)->map(function (Media $item) {
            return [
                'name' => $item->file_name,
                'url' => url($item->getUrl()),
                'uuid' => $item->uuid,
                'poster' => $item->getUrl('shrinkage')
            ];
        })->toArray();
    }

    /**
     * 获取单条既定规格附件
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string $collectionName
     * @param  array  $filters
     * @return array
     */
    public function getFirstMediaArray(string $collectionName = 'default', array|callable $filters = []): array
    {
        $medias = $this->getMediaArray($collectionName, $filters);
        if (empty($medias))
            return [];
        return head($medias);
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

    /**
     * 获取一对一附件key
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    public function getHasOneMedia(): array
    {
        if (property_exists($this, 'hasOneMedia')) {
            return $this->hasOneMedia;
        }
        return [];
    }

}