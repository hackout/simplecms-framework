<?php

namespace SimpleCMS\Framework\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use SimpleCMS\Framework\Contracts\SimpleMedia;
use SimpleCMS\Framework\Enums\SystemConfigEnum;
use SimpleCMS\Framework\Traits\MediaAttributeTrait;
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
 * @property mixed $value 参数值
 * @property-read ?string $file 附件
 * @property-read Collection<Media> $media 附件
 */
class SystemConfig extends Model implements SimpleMedia
{
    use MediaAttributeTrait;

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * 附件Key
     */
    const MEDIA_FILE = 'file';

    public $hasOneMedia = ['file'];

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
        'sort_order' => 'integer',
        'options' => 'array'
    ];

    public $appends = ['file', 'value'];

    public $hidden = ['media', 'content', 'created_at', 'updated_at'];

    public function getFileAttribute()
    {
        return $this->getFirstMediaUrl(static::MEDIA_FILE);
    }

    public function getValueAttribute()
    {
        $type = SystemConfigEnum::fromValue($this->type);
        if (empty($this->content)) {
            return $this->convertContentEmpty($type);
        }
        if ($type->isFile()) {
            return $this->file;
        }
        return $this->convertContentValue($type);
    }

    private function convertContentEmpty(SystemConfigEnum $type)
    {
        return match ($type) {
            SystemConfigEnum::Switch => false,
            SystemConfigEnum::List => [],
            SystemConfigEnum::Checkbox => [],
            default => null
        };
    }

    private function convertContentValue(SystemConfigEnum $type)
    {
        return match ($type) {
            SystemConfigEnum::Switch => (bool) $this->content,
            SystemConfigEnum::List => json_decode($this->content, true),
            SystemConfigEnum::Checkbox => json_decode($this->content, true),
            SystemConfigEnum::Radio => (int) $this->content,
            SystemConfigEnum::Select => (int) $this->content,
            default => $this->content
        };
    }

}
