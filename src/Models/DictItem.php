<?php

namespace SimpleCMS\Framework\Models;

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
 * @property-read ?string $thumbnail 附件LOGO
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
        'thumbnail' => 'array'
    ];

    public $appends = ['value','thumbnail'];

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
        return $this->getFirstMediaUrl(self::MEDIA_FILE);
    }
}
