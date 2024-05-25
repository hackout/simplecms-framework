<?php
namespace SimpleCMS\Framework\Traits;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

/**
 * 变更自增ID为UUID
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
trait PrimaryKeyUuidTrait
{

    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootPrimaryKeyUuidTrait()
    {
        //变更自增ID 为 UUID
        static::creating(function (Model $model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }
}