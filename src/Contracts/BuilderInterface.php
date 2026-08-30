<?php

namespace SimpleCMS\Framework\Contracts;

use Illuminate\Database\Eloquent\{Model,Builder};
use Illuminate\Database\Query\Builder as DatabaseBuilder;

/**
 * @mixin \SimpleCMS\Framework\Services\BaseService
 *
 * @method void prepareToAttachMedia(Media $media, FileAdder $fileAdder)
 *
 * @property bool $registerMediaConversionsUsingModelInstance
 * @property ?\Spatie\MediaLibrary\MediaCollections\MediaCollection $mediaCollections
 */
interface BuilderInterface
{

    /**
     * 获取Builder
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return Builder|Model|null
     */
    public function getBuilder();

    /**
     * DB请求
     * 
     * @param ?string $tableName
     * @return DatabaseBuilder
     */
    public function db(string $tableName = null): DatabaseBuilder;

}
