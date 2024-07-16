<?php
namespace SimpleCMS\Framework\Traits;

use SimpleCMS\Framework\Models\RequestLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * 增加请求日志
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 *
 * 模块class中引用:
 *
 *   use \SimpleCMS\Framework\Traits\RequestLogTrait;
 * 
 * @property-read Collection<RequestLog> $request_logs 请求日志
 * 
 * @use \Illuminate\Database\Eloquent\Model
 * @abstract \Illuminate\Database\Eloquent\Model
 *
 */
trait RequestLogTrait
{

    /**
     * 请求日志
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return MorphMany
     */
    public function request_logs(): MorphMany
    {
        return $this->morphMany(RequestLog::class, 'model');
    }
}