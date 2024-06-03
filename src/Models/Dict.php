<?php

namespace SimpleCMS\Framework\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * 字典模型
 * @author Dennis Lui <hackout@vip.qq.com>
 *
 * @property int $id 组件
 * @property string $code 标识
 * @property string $name 名称
 * @property-read Collection<DictItem> $items 字典项
 */
class Dict extends Model
{
    public $timestamp = false;

    protected $fillable = [
        'code',
        'name',
        'sort_order'
    ];

    public $casts = [
        'sort_order' => 'integer'
    ];

    public function items()
    {
        return $this->hasMany(DictItem::class);
    }
}
