<?php

namespace SimpleCMS\Framework\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\{Model,Collection};

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
 * @property-read ?int $children_count 子级数量
 */
class Role extends Model
{

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

    /**
     * 模型获取角色权限
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  object $model
     * @return array
     */
    public static function getRolesByModel(object $model): array
    {
        $roles = [];
        if ($model instanceof Model) {
            $list = DB::table('roles_more')->where([
                'model_type' => get_class($model),
                'model_id' => $model->{$model->getPrimaryKey()}
            ])->get()->pluck('role_id')->toArray();
            if ($list) {
                $roleList = self::whereIn('id', $list)->get()->pluck('routes')->toArray();
                $roles = array_unique(Arr::collapse($roleList));
            }
        }
        return $roles;
    }
}
