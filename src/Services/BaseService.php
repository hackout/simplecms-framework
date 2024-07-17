<?php

namespace SimpleCMS\Framework\Services;

use function is_null;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * 服务类-核心类
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 *
 * @property string $className 模型路径
 * @property string|null $primaryKey 模型主键
 * @property string|null $orderKey 排序主键 (默认$primaryKey)
 * @property string<'desc','asc'> $orderType 排序 (默认desc)
 * 
 * @property Model|null $item 单条数据,仅在create或update后存在
 * 
 */

class BaseService
{
    protected string $className = null;

    private array $query = [];

    private array $with = [];

    private array $has = [];

    private array $group = [];

    private array $select = [];

    private ?string $tableName = null;

    private ?Model $model = null;

    private ?string $primaryKey;

    private ?string $orderKey;

    private string $orderType = 'desc';

    private mixed $item = null;

    public function __construct()
    {
        if ($this->className) {
            $this->setModel($this->className);
        }
    }

    protected function newModel()
    {
        return new $this->className;
    }

    private function autoModel()
    {
        $this->primaryKey = optional($this->getModel())->getKeyName();
        $this->orderKey = optional($this->getModel())->timestamps ? $this->autoOrderKey() : $this->primaryKey;
        $this->tableName = optional($this->getModel())->getTable();
    }

    /**
     * 设置请求模型
     *
     * @param  string $className
     * @return self
     */
    public function setModel(string $className)
    {
        $this->model = new $className;
        if ($this->model) {
            $this->autoModel();
        }
        return $this;
    }

    /**
     * 获取模型
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getModel(): Model|null
    {
        return $this->model;
    }

    /**
     * 检测sort_order
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return string
     */
    private function autoOrderKey(): string
    {
        if (!is_null($this->model)) {
            if (in_array('sort_order', $this->model->getFillable())) {
                return 'sort_order';
            }
        }
        return 'created_at';
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return (string) $this->className;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return (string) $this->tableName;
    }

    public function getPrimaryKey(): string
    {
        return (string) $this->primaryKey;
    }

    /**
     * 获取当前单条
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return mixed
     */
    public function getItem(string $className = null): mixed
    {
        if (!$className) {
            $className = $this->className;
        }
        if ($this->item instanceof $className) {
            return $this->item;
        }
        return null;
    }

    /**
     * 设置单条信息
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  mixed $item
     * @return void
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * 设置查询参数
     *
     * @param callable|null|array|Expression $query
     * 
     * @return self
     */
    public function setQuery(callable|null|array|Expression $query = null)
    {
        $this->query = Set\Query::run($this->query, $query);
        return $this;
    }

    /**
     * 获取请求查询
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * 设置Group
     *
     * @param callable|null|array|Expression $query
     * 
     * @return self
     */
    public function setGroup(callable|null|array|Expression $group)
    {
        $this->group = Set\Group::run($this->group, $group);
        return $this;
    }

    /**
     * 获取Group
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    public function getGroup(): array
    {
        return $this->group;
    }

    /**
     * 设置Select
     *
     * @param callable|null|array|Expression $select
     * 
     * @return self
     */
    public function setSelect(callable|null|array|Expression $select)
    {
        $this->select = Set\Select::run($this->select, $select);
        return $this;
    }

    /**
     * 获取Select
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    public function getSelect(): array
    {
        return $this->select;
    }

    /**
     * 设置With
     *
     * @param array|string $with
     * 
     * @return $this
     */
    public function setWith(array|string $with)
    {
        $this->with = $with;
        return $this;
    }

    /**
     * 获取With
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array|string
     */
    public function getWith()
    {
        return $this->with;
    }

    /**
     * 设置Has
     *
     * @param null|array|Relation|string $has
     * 
     * @return $this
     */
    public function setHas(null|array|Relation|string $has = null)
    {
        $this->has = Set\Has::run($this->has, $has);
        return $this;
    }

    /**
     * 获取Has
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    public function getHas()
    {
        return $this->has;
    }


    /**
     * 清除所有附加请求参数
     * 
     * @return self
     */
    public function clearCondition(): self
    {
        $this->group = [];
        $this->query = [];
        $this->has = [];
        $this->select = [];
        $this->with = [];
        return $this;
    }

    /**
     * @param string $orderKey
     * @param string $orderType
     * @return static
     */
    public function sortBy(string $orderKey, string $orderType = 'desc')
    {
        $this->setOrderKey($orderKey);
        $this->setOrderType($orderType);
        return $this;
    }

    /**
     * @param string $orderKey
     * @return static
     */
    public function setOrderKey(string $orderKey)
    {
        $this->orderKey = $orderKey;
        return $this;
    }

    /**
     * @param string $orderType
     * @return static
     */
    public function setOrderType(string $orderType)
    {
        $this->orderType = $orderType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOrderKey()
    {
        return $this->orderKey;
    }

    /**
     * @return string
     */
    public function getOrderType()
    {
        return $this->orderType;
    }
}
