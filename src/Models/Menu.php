<?php

namespace SimpleCMS\Framework\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use SimpleCMS\Framework\Traits\SimpleTreeTrait;
use SimpleCMS\Framework\Traits\MediaAttributeTrait;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * 字典项模型
 * @author Dennis Lui <hackout@vip.qq.com>
 *
 * @property int $id ID
 * @property string $name 角色名
 * @property array $url URL
 * @property bool $is_valid 是否生效
 * @property bool $is_show 是否显示
 * @property ?string $icon 图标
 * @property bool $can_delete 是否允许删除
 * @property ?int $sort_order 排序
 * @property ?int $parent_id 父级ID
 * @property-read ?Collection<Media> $media 附件
 * @property-read ?string $thumbnail 附件LOGO
 * @property-read ?Collection<self> $children 子级
 * @property-read ?self $parent 上级
 */

class Menu extends Model implements HasMedia
{
    use SimpleTreeTrait, MediaAttributeTrait;
    public $timestamps = false;

    /**
     * 附件Key
     */
    const MEDIA_FILE = 'file';

    /**
     * 后台菜单
     */
    const TYPE_BACKEND = 1;

    /**
     * 前台菜单
     */
    const TYPE_FRONTEND = 2;

    public array $hasOneMedia = ['file'];

    protected $fillable = [
        'name',
        'url',
        'type',
        'can_delete',
        'icon',
        'is_valid',
        'is_show',
        'sort_order',
        'parent_id',
    ];

    public $casts = [
        'url' => 'array',
        'is_valid' => 'boolean',
        'is_show' => 'boolean',
        'can_delete' => 'boolean',
        'type' => 'integer',
        'sort_order' => 'integer',
        'parent_id' => 'integer',
    ];

    public $appends = ['thumbnail'];

    public $hidden = [
        'media'
    ];

    public function getThumbnailAttribute()
    {
        return $this->getFirstMediaUrl(self::MEDIA_FILE);
    }
}
