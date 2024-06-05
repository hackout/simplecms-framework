<?php

namespace SimpleCMS\Framework\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * 系统设置
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 * @property string $code 主键Key
 * @property string $name 名称
 * @property string $description 说明
 * @property string $content 内容
 * @property string $type 类型
 * @property ?array $options 选项参数
 * @property-read ?string $file 附件
 * @property-read Collection<Media> $media 附件
 */
class SystemConfig extends Model implements HasMedia
{
    use InteractsWithMedia;

    public $incrementing = false;

    /**
     * 附件Key
     */
    const MEDIA_FILE = 'file';

    /**
     * 单行文本
     */
    const TYPE_INPUT = 'input';

    /**
     * 多行文本
     */
    const TYPE_TEXTAREA = 'textarea';

    /**
     * 富文本
     */
    const TYPE_EDITOR = 'editor';

    /**
     * 文件
     */
    const TYPE_FILE = 'file';

    /**
     * 图片
     */
    const TYPE_IMAGE = 'image';

    /**
     * 单选项
     */
    const TYPE_RADIO = 'radio';

    /**
     * 下拉选项
     */
    const TYPE_SELECT = 'select';

    /**
     * 开关
     */
    const TYPE_SWITCH = 'switch';

    /**
     * 多选项
     */
    const TYPE_CHECKBOX = 'checkbox';

    /**
     * 列表
     */
    const TYPE_LIST = 'list';

    protected $fillable = [
        'code',
        'name',
        'description',
        'content',
        'type',
        'options',
        'sort_order',
    ];

    public $casts = [
        'sort_order' => 'integer'
    ];

    public function getKeyType()
    {
        return 'string';
    }

    public $appends = ['file'];

    public $hidden = ['media'];

    public function getFileAttribute()
    {
        return $this->getFirstMediaUrl();
    }
}
