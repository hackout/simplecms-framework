<?php

namespace SimpleCMS\Framework\Models;


use Illuminate\Support\Facades\DB;
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
 * @property string $name 角色名
 * @property string $description 角色说明
 * @property string $type 角色类型
 * @property string $routes 路由
 * @property string $extra 附加参数
 * @property-read ?Collection<Media> $media 附件
 * @property-read ?string $thumbnail 附件LOGO
 * @property-read ?int $children_count 子级数量
 */
class Role extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * 附件KEY
     */
    const MEDIA_FILE = "file";

    /**
     * 后台角色
     */
    const TYPE_BACKEND = 1;

    /**
     * 前台角色
     */
    const TYPE_FRONTEND = 2;

    protected $fillable = [
        'name',
        'description',
        'type',
        'routes',
        'extra',
        'sort_order'
    ];

    public $casts = [
        'type' => 'integer',
        'sort_order' => 'integer',
        'routes' => 'array',
        'extra' => 'array'
    ];


    public $appends = ['thumbnail'];

    public $hidden = [
        'media'
    ];

    /**
     * 获取关联子级
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return Collection|null
     */
    public function children(): ?Collection
    {
        $relations = DB::table('roles_more')->where('role_id', $this->id)->get();
        if (!$relations->count())
            return null;
        $modelClass = $relations->first()->model_type;
        $modelIdList = $relations->pluck('model_id')->toArray();
        return $modelClass::whereIn((new $modelClass)->getPrimaryKey(), $modelIdList)->get();
    }

    public function getChildrenCountAttribute()
    {
        return DB::table('roles_more')->where('role_id', $this->id)->count();
    }

    public function getThumbnailAttribute()
    {
        return $this->getFirstMediaUrl(self::MEDIA_FILE);
    }
}
