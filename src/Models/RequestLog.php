<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use SimpleCMS\Framework\Traits\PrimaryKeyUuidTrait;

/**
 * 请求日志
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * @property string $id 主键
 * @property ?string $model_id 父级ID
 * @property ?string $model_type 父级模型
 * @property ?string $name 说明
 * @property ?string $ip_address IP
 * @property ?array $user_agent UserAgent
 * @property string $method 请求方法
 * @property string $url 请求地址
 * @property ?array $parameters 请求参数
 * @property string $route_name 路由别名
 * @property bool $status 请求状态
 * @property-read Carbon $created_at 发生时间
 * @property-read ?MorphTo $model 模型
 */
class RequestLog extends Model
{
    use PrimaryKeyUuidTrait;

    /**
     * 删除更新时间
     */
    const UPDATED_AT = null;

    /**
     * 可输入字段
     */
    protected $fillable = [
        'id',
        'model_id',
        'model_type',
        'name',
        'ip_address',
        'user_agent',
        'method',
        'url',
        'parameters',
        'route_name',
        'status'
    ];

    /**
     * 显示字段类型
     */
    public $casts = [
        'user_agent' => 'array',
        'parameters' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status' => 'boolean'
    ];

    /**
     * 隐藏关系
     */
    public $hidden = ['model'];

    /**
     * 上级
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return MorphTo
     */
    public function model()
    {
        return $this->morphTo();
    }

}
