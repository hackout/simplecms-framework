<?php

namespace SimpleCMS\Framework\Traits;

use Exception;
use Illuminate\Database\Eloquent\Collection;
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
 * 
 * @use SimpleTreeTrait
 * 
 * @abstract SimpleTreeTrait
 * 
 */
trait SimpleTreeScopeTrait
{

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
        $columns = $this->getColumns($column, $key);
        $collection = $query->getQuery()->get($columns);

        list($pairMap, $rootItems) = $this->buildParentChildMap($collection);

        return $this->buildNestedCollection($rootItems, $pairMap, $column, $key, $indent);
    }

    /**
     * Get the columns needed for the query.
     *
     * @param string $column
     * @param string|null $key
     * @return array
     */
    protected function getColumns($column, $key)
    {
        /** @scrutinizer ignore-call */
        $idName = $this->getKeyName();

        $parentName = $this->getParentColumnName();

        $columns = [$idName, $parentName, $column];
        if ($key !== null) {
            $columns[] = $key;
        }

        return $columns;
    }

    /**
     * Build a map of parent-child relationships.
     *
     * @param \Illuminate\Support\Collection $collection
     * @return array
     */
    protected function buildParentChildMap($collection)
    {
        $parentName = $this->getParentColumnName();
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

        return [$pairMap, $rootItems];
    }

    /**
     * Recursively build the nested collection.
     *
     * @param array $items
     * @param array $map
     * @param string $column
     * @param string|null $key
     * @param string $indent
     * @param int $depth
     * @return array
     */
    protected function buildNestedCollection($items, $map, $column, $key, $indent, $depth = 0)
    {
        $result = [];
        $indentString = str_repeat($indent, $depth);
        $idName = $this->getKeyName();

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
            $childItems = $map instanceof Collection ? $map->get($item->{$idName}, []) : collect($map)->get($item->{$idName}, []);
            if (count($childItems) > 0) {
                $result = $result + $this->buildNestedCollection($childItems, $map, $column, $key, $indent, $depth + 1);
            }
        }

        return $result;
    }


}
