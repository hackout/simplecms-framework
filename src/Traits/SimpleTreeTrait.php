<?php

namespace SimpleCMS\Framework\Traits;

use Illuminate\Database\Eloquent\Collection;
use SimpleCMS\Framework\Database\TreeCollection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 简易树型结构
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 *
 * 说明:
 *
 * 模块中必须存在parent_id父级ID
 * 模块class中引用:
 *
 *   use \SimpleCMS\Framework\Traits\SimpleTreeTrait;
 *
 * 模型方法:
 *
 *   $model->getChildren(); // Returns children of this node
 *   $model->getChildCount(); // Returns number of all children.
 *   $model->getAllChildren(); // Returns all children of this node
 *   $model->getAllRoot(); // Returns all root level nodes (eager loaded)
 *   $model->getAll(); // Returns everything in correct order.
 *
 * 请求查询方法:
 *
 *   $query->listsNested(); // Returns an indented array of key and value columns.
 *
 * 你可以重新定义父级ID的字段:
 * 
 * @property-read mixed $parent 上级
 * @property-read mixed $children 下级
 * 
 * @use \Illuminate\Database\Eloquent\Model
 * @use \Illuminate\Database\Eloquent\Concerns\HasRelationships
 *
 * @static string PARENT_KEY
 */
trait SimpleTreeTrait
{

    use SimpleTreeScopeTrait;

    public static function bootSimpleTreeTrait()
    {
        static::deleting(function ($model) {
            $model->children->each(fn($child) => $child->delete());
        });
    }

    /**
     * 上级
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return HasMany
     */
    public function parent(): BelongsTo
    {
        return $this->/** @scrutinizer ignore-call */ belongsTo(static::class, $this->getParentColumnName());
    }

    /**
     * 下级
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->/** @scrutinizer ignore-call */ hasMany(static::class, $this->getParentColumnName());
    }

    /**
     * 返回所有列表
     * @return Collection
     */
    public function getAll()
    {
        $collection = [];
        foreach ($this->/** @scrutinizer ignore-call */ getAllRoot() as $rootNode) {
            $collection[] = $rootNode;
            $collection = $collection + $rootNode->getAllChildren()->getDictionary();
        }

        return new Collection($collection);
    }

    /**
     * 获取所有子级
     * @return Collection
     */
    public function getAllChildren()
    {
        $result = [];
        $children = $this->getChildren();

        foreach ($children as $child) {
            $result[] = $child;

            $childResult = $child->getAllChildren();
            foreach ($childResult as $subChild) {
                $result[] = $subChild;
            }
        }

        return new Collection(/** @scrutinizer ignore-type */ $result);
    }

    /**
     * 子级
     * @return Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * 统计子级数量
     * @return int
     */
    public function getChildCount()
    {
        return $this->getAllChildren()->count();
    }

    /**
     * 获取所有父级
     * in multiple queries.
     */
    public function getParents()
    {
        $result = [];

        $parent = $this;
        $result[] = $parent;

        while ($parent = $parent->parent) {
            $result[] = $parent;
        }

        return array_reverse($result);
    }

    /**
     * 获取父级字段名
     * @return string
     */
    public function getParentColumnName()
    {
        return defined(static::class . '::PARENT_KEY') ? static::PARENT_KEY : 'parent_id';
    }

    /**
     * 获取父级SQL标识
     * @return string
     */
    public function getQualifiedParentColumnName()
    {
        return /** @scrutinizer ignore-call */ $this->getTable() . '.' . $this->getParentColumnName();
    }

    /**
     * 获取父级ID
     * @return int|string
     */
    public function getParentId()
    {
        return $this->/** @scrutinizer ignore-call */ getAttribute($this->getParentColumnName());
    }

    /**
     * newCollection returns a custom TreeCollection collection.
     */
    public function newCollection(array $models = [])
    {
        return new TreeCollection(/** @scrutinizer ignore-type */ $models);
    }
}
