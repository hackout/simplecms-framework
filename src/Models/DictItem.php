<?php

namespace SimpleCMS\Framework\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use SimpleCMS\Framework\Contracts\SimpleMedia;
use SimpleCMS\Framework\Traits\MediaAttributeTrait;
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
class DictItem extends Model implements SimpleMedia
{
    use MediaAttributeTrait;

    const MEDIA_FILE = 'file';

    protected $hasOneMedia = ['file'];

    protected $fillable = [
        'dict_id',
        'name',
        'content',
        'sort_order'
    ];

    public $casts = [
        'sort_order' => 'integer',
        'value' => 'integer',
        'thumbnail' => 'array'
    ];

    public $appends = ['value', 'thumbnail'];

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

    public function getThumbnailAttribute()
    {
        return $this->getFirstMediaUrl(static::MEDIA_FILE);
    }
}
