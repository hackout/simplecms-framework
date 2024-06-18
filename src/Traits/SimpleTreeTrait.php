<?php

namespace SimpleCMS\Framework\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SimpleCMS\Framework\Database\TreeCollection;
use Exception;

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
 *   const PARENT_ID = 'my_parent_column';
 * @property-read mixed $parent 上级
 * @property-read mixed $children 下级
 */
trait SimpleTreeTrait
{

    /**
     * 上级
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return HasMany
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, $this->getParentColumnName());
    }

    /**
     * 下级
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(static::class, $this->getParentColumnName());
    }

    /**
     * 返回所有列表
     * @return Collection
     */
    public function getAll()
    {
        $collection = [];
        foreach ($this->getAllRoot() as $rootNode) {
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

        return new Collection($result);
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
     * 获取全部父级
     * $model->getRoot()
     * @return Collection
     */
    public function scopeGetAllRoot($query)
    {
        return $query->where($this->getParentColumnName(), null)->get();
    }

    /**
     * 加入树型请求
     * Children are eager loaded inside the $model->children relation.
     * @return Collection A collection
     */
    public function scopeGetNested($query)
    {
        return $query->get()->toNested();
    }

    /**
     * scopeListsNested gets an array with values of a given column. Values are indented
     * according to their depth.
     * @param  string $column Array values
     * @param  string $key    Array keys
     * @param  string $indent Character to indent depth
     * @return array
     */
    public function scopeListsNested($query, $column, $key = null, $indent = '&nbsp;&nbsp;&nbsp;')
    {
        $idName = $this->getKeyName();
        $parentName = $this->getParentColumnName();

        $columns = [$idName, $parentName, $column];
        if ($key !== null) {
            $columns[] = $key;
        }

        $collection = $query->getQuery()->get($columns);

        // Assign all child nodes to their parents
        $pairMap = [];
        $rootItems = [];
        foreach ($collection as $record) {
            if ($parentId = $record->{$parentName}) {
                if (!isset($pairMap[$parentId])) {
                    $pairMap[$parentId] = [];
                }
                $pairMap[$parentId][] = $record;
            } else {
                $rootItems[] = $record;
            }
        }

        // Recursive helper function
        $buildCollection = function ($items, $map, $depth = 0) use (&$buildCollection, $column, $key, $indent, $idName) {
            $result = [];

            $indentString = str_repeat($indent, $depth);

            foreach ($items as $item) {
                if (!property_exists($item, $column)) {
                    throw new Exception('Column mismatch in listsNested method. Are you sure the columns exist?');
                }

                if ($key !== null) {
                    $result[$item->{$key}] = $indentString . $item->{$column};
                } else {
                    $result[] = $indentString . $item->{$column};
                }

                // Add the children
                $childItems = $map instanceof Collection ? $map->get($item->{$idName}, []) : collect($map->get($item->{$idName}, []));
                if (count($childItems) > 0) {
                    $result = $result + $buildCollection($childItems, $map, $depth + 1);
                }
            }

            return $result;
        };

        // Build a nested collection
        return $buildCollection($rootItems, $pairMap);
    }

    /**
     * 获取父级字段名
     * @return string
     */
    public function getParentColumnName()
    {
        return defined('static::PARENT_ID') ? static::PARENT_ID : 'parent_id';
    }

    /**
     * 获取父级SQL标识
     * @return string
     */
    public function getQualifiedParentColumnName()
    {
        return $this->getTable() . '.' . $this->getParentColumnName();
    }

    /**
     * 获取父级ID
     * @return int|string
     */
    public function getParentId()
    {
        return $this->getAttribute($this->getParentColumnName());
    }

    /**
     * newCollection returns a custom TreeCollection collection.
     */
    public function newCollection(array $models = [])
    {
        return new TreeCollection($models);
    }
}
