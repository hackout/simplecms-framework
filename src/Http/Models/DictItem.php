<?php

namespace SimpleCMS\Framework\Http\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * 字典项模型
 * @author Dennis Lui <hackout@vip.qq.com>
 *
 * @property int $id ID
 * @property int $dict_id 字典ID
 * @property string $name 键名
 * @property int $content 键值
 * @property-read int $value 键值
 * @property-read ?Collection<Media> $media 附件
 * @property-read ?array<array<string,string>> $thumbnails 附件记录
 */
class DictItem extends Model implements HasMedia
{
    use InteractsWithMedia;

    const MEDIA_FILE = 'file';

    protected $fillable = [
        'dict_id',
        'name',
        'content',
    ];

    public $casts = [
        'value' => 'integer',
        'thumbnails' => 'array'
    ];

    public $appends = ['value','thumbnails'];

    public $hidden = [
        'content',
        'dict_id',
        'media'
    ];

    public function dict()
    {
        return $this->belongsTo(Dict::class);
    }

    public function getValueAttribute()
    {
        return (int) $this->content;
    }
    
    public function getThumbnailsAttribute()
    {
        if (!$medias = $this->getMedia(self::MEDIA_FILE))
            return [];
        return $medias->map(function ($item) {
            return [
                'name' => $item->file_name,
                'url' => $item->original_url,
                'uuid' => $item->uuid
            ];
        });
    }
}
