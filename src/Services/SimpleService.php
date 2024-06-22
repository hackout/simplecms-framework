<?php

namespace SimpleCMS\Framework\Services;

use function is_array;
use function array_pad;
use function is_string;
use function is_numeric;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use SimpleCMS\Framework\Traits\ServiceMacroable;
use SimpleCMS\Framework\Exceptions\SimpleException;

/**
 * 基础服务类
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
 * @method self setModel(string $className)
 * @method self setQuery(callable|null|array $query)
 * @method self appendQuery(callable|null|array $query)
 * @method callable|null|array getQuery()
 * @method self listQuery(array $data = null, array $keys, array $sqlList = [])
 * @method self setWith(callable|null|array $with)
 * @method self setHas(null|array $has)
 * @method self setGroup(array|string|null $group)
 * @method self setSelect(callable|null|array $select)
 * @method self setSelectRaw(callable|null|array $select)
 * @method self appendWith(callable|null|array $with)
 * @method self appendHas(null|array $has)
 * @method self appendGroup(array|string|null $group)
 * @method self appendSelect(callable|null|array $select)
 * @method callable|null|array getWith()
 * @method null|array getHas()
 * @method array|string|null getGroup()
 * @method callable|null|array getSelect()
 * @method callable|null|array getSelectRaw()
 * @method self clearAddons()
 * @method Collection|null getAll(array $fieldList = [])
 * @method array|null list(array $fieldList = [])
 * @method bool create(array $data)
 * @method bool update(string|int $id, array $data)
 * @method bool delete(string|int $id)
 * @method void clean()
 * @method bool batch_delete(array $ids)
 * @method Model|null findById(string|int $id)
 * @method Model|null find(callable|null|array $where = null)
 * @method bool setValue(string|int|array $id, string $field, string|float|array $value)
 * @method bool quick(array $data)
 * 
 * 
 * @method self queryDistance(float $lat,float $lng,float $maxDistance,string $geoColumn)
 * @method self selectDistance(float $lat,float $lng,string $geoColumn,string $alias)
 * @see \SimpleCMS\Region\Services\DistanceService
 * 
 */
class SimpleService
{
    use ServiceMacroable;

    public ?string $className = null;

    protected $query;

    protected $with;

    protected $has;

    protected $group;

    protected $select;

    protected $selectRaw;

    protected $cacheName;

    public null|Model $model = null;

    public string $primaryKey;

    public string $orderKey;

    public string $orderType = 'desc';

    public null|Model $item = null;

    public function __construct()
    {
        if ($this->className) {
            $this->model = new $this->className;
            $this->primaryKey = $this->model->getKeyName();
            $this->orderKey = $this->model->timestamps ? $this->autoOrderKey() : $this->primaryKey;
            $this->cacheName = $this->model->getTable();
        }
    }

    /**
     * 检测sort_order
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return string
     */
    protected function autoOrderKey(): string
    {
        if (in_array('sort_order', $this->model->getFillable())) {
            return 'sort_order';
        }
        return 'created_at';
    }

    /**
     * 上传文件/图片/视频
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return SimpleUploadService
     */
    public function upload(): SimpleUploadService
    {
        return new SimpleUploadService;
    }

    /**
     * 请求当前单条信息
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return mixed
     */
    public function getItem(): mixed
    {
        return $this->item;
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

    private function newModel()
    {
        return new $this->className;
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
        $this->primaryKey = $this->model->getKeyName();
        $this->orderKey = $this->primaryKey;
        return $this;
    }

    /**
     * 设置查询参数
     *
     * @param callable|null|array $query
     * 
     * @return self
     */
    public function setQuery(callable|null|array $query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * 设置查询参数
     *
     * @param callable|null|array $query
     * 
     * @return self
     */
    public function appendQuery(callable|null|array $query)
    {
        if (is_array($this->query)) {
            $this->query = array_merge($this->query, $query);
        }
        if (is_callable($this->query)) {
            $this->query[] = [$this->query];
            $this->query = array_merge($this->query, $query);
        }
        if (!$this->query) {
            $this->query = $query;
        }
        return $this;
    }

    /**
     * 获取请求查询
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * 进阶搜索
     * 
     * @param  ?array $data 查询条件
     * @param  array<string,string|array<string>>      $conditions 查询动作方式
     * @param  ?array      $otherConditions 附加条件
     * @return self
     */
    public function listQuery(array $data = null, array $conditions, array $otherConditions = [])
    {
        if ($data) {
            $sql = $otherConditions ?: [];
            foreach ($conditions as $key => $value) {
                $values = is_array($value) ? $value : [$value];
                list($action, $fields, $extra) = array_pad($values, 3, null);
                if (!$fields)
                    $fields = $key;
                if ($action == 'search' && array_key_exists($key, $data) && trim($data[$key])) {
                    $keyword = trim($data[$key]);
                    $sql[] = Query\Search::builder($keyword, $fields, $extra ?? false);
                }

                if ($action == 'datetime_range' && array_key_exists($key, $data) && $data[$key]) {
                    tap(Query\DateTimeRange::builder($data[$key], $fields, $extra ?? false), function (array $_sql) use (&$sql) {
                        $sql[] = $_sql;
                    });
                }
                if ($action == 'range' && array_key_exists($key, $data) && $data[$key]) {
                    tap(Query\Range::builder($data[$key], $fields, $extra ?? false), function (array $_sql) use (&$sql) {
                        $sql[] = $_sql;
                    });
                }
                if ($action == 'datetime' && array_key_exists($key, $data) && $data[$key]) {
                    $sql[] = Query\Search::builder($data[$key], $fields, $extra ?? false);
                }
                if ($action == 'date' && array_key_exists($key, $data) && $data[$key]) {
                    $sql[] = Query\Date::builder($data[$key], $fields, $extra ?? false);
                }
                if ($action == 'year' && array_key_exists($key, $data) && $data[$key]) {
                    $sql[] = Query\Year::builder($data[$key], $fields, $extra ?? false);
                }
                if ($action == 'month' && array_key_exists($key, $data) && $data[$key]) {
                    $sql[] = Query\Month::builder($data[$key], $fields, $extra ?? false);
                }
                if ($action == 'day' && array_key_exists($key, $data) && $data[$key]) {
                    $sql[] = Query\Day::builder($data[$key], $fields, $extra ?? false);
                }
                if ($action == 'in' && array_key_exists($key, $data) && $data[$key]) {
                    $sql[] = Query\In::builder($data[$key], $fields);
                }
                if ($action == 'eq' && array_key_exists($key, $data) && $data[$key] !== null) {
                    $sql[] = Query\Eq::builder($data[$key], $fields);
                }
                if ($action == 'like' && array_key_exists($key, $data) && $data[$key] !== null) {
                    $sql[] = Query\Like::builder($data[$key], $fields);
                }
            }
            if ($sql) {
                $this->setQuery($sql);
            }
        }
        return $this;
    }

    /**
     * 设置GroupBy
     *
     * @param array|string|null $group
     * 
     * @return self
     */
    public function setGroup(array|string|null $group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * 追加Group
     *
     * @param callable|null|array $group
     * 
     * @return self
     */
    public function appendGroup(array|string|null $group)
    {
        if (is_array($this->group)) {
            $this->group = array_merge($this->group, $group);
        }
        if (is_string($this->group)) {
            $this->group[] = [$this->group];
            $this->group = array_merge($this->group, $group);
        }
        if (!$this->group) {
            $this->group = $group;
        }
        return $this;
    }

    /**
     * 获取请求查询
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }


    /**
     * 清除附加请求
     * 
     * @return self
     */
    public function clearAddons(): self
    {
        $this->group = null;
        $this->query = null;
        $this->has = null;
        $this->select = null;
        $this->with = null;
        return $this;
    }

    /**
     * 设置SelectRaw
     *
     * @param string|null $selectRaw
     * 
     * @return self
     */
    public function setSelectRaw(string|null $selectRaw)
    {
        $this->selectRaw = $selectRaw;
        return $this;
    }

    /**
     * 获取SelectRaw
     * 
     * @return string|null
     */
    public function getSelectRaw()
    {
        return $this->selectRaw;
    }

    /**
     * 设置Select
     *
     * @param callable|null|array $select
     * 
     * @return self
     */
    public function setSelect(callable|null|array $select)
    {
        $this->select = $select;
        return $this;
    }

    /**
     * 追加Select
     *
     * @param callable|null|array $select
     * 
     * @return self
     */
    public function appendSelect(array|string|null $select)
    {
        if (is_array($this->select)) {
            $this->select = array_merge($this->select, $select);
        }
        if (is_callable($this->select)) {
            $this->select[] = [$this->select];
            $this->select = array_merge($this->select, $select);
        }
        if (!$this->select) {
            $this->select = $select;
        }
        return $this;
    }

    /**
     * 获取请求查询
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return mixed
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * 设置with
     *
     * @param callable|null|array $with
     * 
     * @return self
     */
    public function setWith(callable|null|array $with)
    {
        $this->with = $with;
        return $this;
    }

    /**
     * 追加With
     *
     * @param callable|null|array $with
     * 
     * @return self
     */
    public function appendWith(array|string|null $with)
    {
        if (is_array($this->with)) {
            $this->with = array_merge($this->with, $with);
        }
        if (is_callable($this->with)) {
            $this->with[] = [$this->with];
            $this->with = array_merge($this->with, $with);
        }
        if (!$this->with) {
            $this->with = $with;
        }
        return $this;
    }

    /**
     * 获取请求查询
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return mixed
     */
    public function getWith()
    {
        return $this->with;
    }

    /**
     * 设置has
     *
     * @param null|array $has
     * 
     * @return self
     */
    public function setHas(null|array $has): self
    {
        $this->has = $has;
        return $this;
    }


    /**
     * 追加Has
     *
     * @param null|array $has
     * 
     * @return self
     */
    public function appendHas(null|array $has)
    {
        if (is_array($this->has)) {
            $this->has = array_merge($this->has, $has);
        }
        if (!$this->has) {
            $this->has = $has;
        }
        return $this;
    }

    /**
     * 获取请求查询
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return mixed
     */
    public function getHas()
    {
        return $this->has;
    }

    /**
     * 获取所有参数
     *
     * @param array<string> $fieldList 显示选项
     *
     * @return Collection|null
     */
    public function getAll(array $fieldList = [])
    {
        $builder = $this->builder();
        $result = $builder->get();

        if (!$result) {
            return null;
        }

        return $this->filterData($result, $fieldList);
    }


    /**
     * 过滤数据
     *
     * @param Collection|\Illuminate\Support\Collection $data
     * @param array $fieldList
     * @return Collection|\Illuminate\Support\Collection
     */
    private function filterData(Collection|\Illuminate\Support\Collection $data, $fieldList)
    {

        return $data->map(function (Model $item) use ($fieldList) {
            if (!$fieldList)
                return $item;
            $newItem = collect();
            foreach ($fieldList as $value) {
                list($key, $val) = array_pad(explode(' as ', strtolower($value)), 2, null);
                list($relation, $key1) = array_pad(explode('.', strtolower($key)), 2, null);
                $key1 = $key1 ?: $relation;
                $val = $val ?: $key1;
                if ($relation === $key) {
                    if (strpos($relation, ':') === false) {
                        $newItem->put($val, $item->{$key});
                    } else {
                        list($relation, $relationColumnString) = explode(':', $relation);
                        list($val, $valColumnString) = array_pad(explode(':', $val), 2, null);
                        if (!$relationColumnString) {
                            throw new SimpleException(trans('simplecms:range_valid'));
                        }
                        $valColumns = $valColumnString ? explode(',', $valColumnString) : null;
                        $relationColumns = explode(',', $relationColumnString);
                        $newItem->put($val, optional($item->{$relation})->map(function ($item) use ($relationColumns, $valColumns) {
                            $newItem = [];
                            foreach ($relationColumns as $index => $key) {
                                $newItem[array_key_exists($index, $valColumns) ? $valColumns[$index] : $key] = $item->{$key};
                            }
                            return $newItem;
                        }));
                    }
                } else {
                    if (strpos($relation, ':') === false) {
                        $newItem->put($val, optional($item->{$relation})->{$key1});
                    } else {
                        list($relation, $relationColumnString) = explode(':', $relation);
                        list($val, $valColumnString) = array_pad(explode(':', $val), 2, null);
                        if (!$relationColumnString) {
                            throw new SimpleException(trans('simplecms:range_valid'));
                        }
                        $valColumns = $valColumnString ? explode(',', $valColumnString) : null;
                        $relationColumns = explode(',', $relationColumnString);
                        $newItem->put($val, optional($item->{$relation})->map(function ($item) use ($relationColumns, $valColumns) {
                            $newItem = [];
                            foreach ($relationColumns as $index => $key) {
                                $newItem[array_key_exists($index, $valColumns) ? $valColumns[$index] : $key] = $item->{$key};
                            }
                            return $newItem;
                        }));
                    }
                }
            }
            return $newItem;
        });
    }

    /**
     * 获取Builder
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return Builder|Model|null
     */
    public function getBuilder()
    {
        return $this->builder();
    }

    private function builder(?string $prop = null, ?string $order = null, )
    {
        $builder = $this->model;
        $timestamps = $builder->timestamps;
        if ($this->query) {
            $builder = $builder->where($this->query);
        }
        if ($this->with) {
            $builder = $builder->with($this->with);
        }
        if ($this->group && is_array($this->group)) {
            $builder = $builder->groupByRaw(implode(',', $this->group));
        }
        if ($this->group && !is_array($this->group)) {
            $builder = $builder->groupBy($this->group);
        }
        if ($this->selectRaw) {
            $builder = $builder->selectRaw($this->selectRaw);
        }
        if ($this->has) {
            foreach ($this->has as $key => $value) {
                $builder = $builder->whereHas($key, function ($q) use ($value) {
                    $q->where($value);
                });
            }
        }
        $builder = (new Work\MakeSelect)->run($this->model, $builder, $this->select);
        if (!$prop && !$order) {
            if ($timestamps) {
                $builder->orderBy($this->orderKey, $this->orderType);
            }
        } else {
            if ($prop && $order) {
                $builder->orderBy($prop, Str::remove('ending', $order));
            }
        }

        return $builder;
    }


    /**
     * 获取分页数据
     *
     * @param ?array<string> $fieldList 显示选项
     * 
     * @return array|null
     */
    public function list(array $fieldList = [])
    {
        $page = request()->get('page', 1);
        $limit = request()->get('limit', 10);
        $prop = request()->get('prop', null);
        $order = request()->get('order', null);
        $builder = $this->builder($prop, $order);
        $data = $this->getCacheData([$builder->toRawSql(), $limit, 'page', $page], fn() => $builder->paginate($limit, ['*'], 'page', $page));
        $items = $this->filterData(collect($data->items()), $fieldList);
        return [
            'items' => $items,
            'total' => $data->total(),
        ];
    }

    /**
     * 读取数据缓存
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array    $array
     * @param  callable $function
     * @return mixed
     */
    protected function getCacheData(array $array, callable $function): mixed
    {
        if (!(new Work\CanCache)->run($this->model))
            return $function();
        $cacheKeyName = $this->cacheName . '_' . $this->getCacheKey($array);
        $cacheName = $this->cacheName;
        return Cache::rememberForever($cacheKeyName, function () use ($function, $cacheName, $cacheKeyName) {
            $cache = Cache::get($cacheName, []);
            $cache[] = $cacheKeyName;
            Cache::forever($cacheName, $cache);
            return $function();
        });
    }

    /**
     * 清空缓存标记
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    protected function clearCacheData()
    {
        $cache = Cache::get($this->cacheName, []);
        foreach ($cache as $key) {
            Cache::forget($key);
        }
        Cache::forget($this->cacheName);
    }

    /**
     * 清空缓存数据
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    public function clearCache()
    {
        $this->clearCacheData();
    }

    /**
     * 获取缓存KeyName
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array  $array
     * @return string
     */
    protected function getCacheKey(array $array): string
    {
        return md5(json_encode($array));
    }

    /**
     * 创建数据
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array<string,mixed> $data 数据参数
     * @param  array<string,string> $mediaFields 附件对应键
     * @return bool
     */
    public function create(array $data, array $mediaFields = [])
    {
        list($sql, $files, $multipleFiles) = app(Work\ConvertData::class)->run($this->model, $data, $mediaFields);

        $this->item = $this->newModel();
        $this->item->fill($sql);
        $result = $this->item->save();
        if ($result) {
            if ($this->hasMedia()) {
                $this->updateMedia($files, $multipleFiles, $mediaFields);
            }
            $this->clearCacheData();
        }

        return $result;
    }

    /**
     * 更新数据
     *
     * @param  string|int $id 主键
     * @param  array<string,mixed> $data 数据参数
     * @param  array<string,string> $mediaFields 附件对应键
     * @return bool
     */
    public function update(string|int $id, array $data, array $mediaFields = [])
    {
        $this->item = $this->model->where($this->primaryKey, $id)->first();

        if (!$this->item) {
            throw new SimpleException(trans('simplecms:not_exists'));
        }

        list($sql, $files, $multipleFiles) = app(Work\ConvertData::class)->run($this->model, $data, $mediaFields);
        $this->item->fill($sql);
        $result = $this->item->save();

        if ($result) {
            if ($this->hasMedia()) {
                $this->updateMedia($files, $multipleFiles, $mediaFields);
            }
            $this->clearCacheData();
        }

        return $result;
    }

    /**
     * 更新附件
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array $files
     * @param  array $multipleFiles
     * @param  array $mediaFields
     * @return void
     */
    protected function updateMedia(array $files, array $multipleFiles, array $mediaFields): void
    {
        if ($files) {
            if (!$mediaFields) {
                $mediaColumn = $this->getMediaColumn() ?? head(array_keys($files));
                $this->addMedia(head($files), $mediaColumn);
            } else {
                foreach ($files as $field => $file) {
                    if (array_key_exists($field, $mediaFields) && $mediaFields[$field]) {
                        $this->addMedia($file, $mediaFields[$field]);
                    }
                }
            }
        }
        if ($multipleFiles) {

            if (!$mediaFields) {
                $mediaColumn = $this->getMediaColumn() ?? head(array_keys($files));
                $this->addMultipleMedia(head($files), $mediaColumn);
            } else {
                foreach ($files as $field => $file) {
                    if (array_key_exists($field, $mediaFields) && $mediaFields[$field]) {
                        $this->addMultipleMedia($file, $mediaFields[$field]);
                    }
                }
            }
        }
    }

    /**
     * 添加附件
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  UploadedFile|string $file
     * @param  string              $columnName
     * @return void
     */
    public function addMedia(UploadedFile|string $file, string $columnName): void
    {
        app(Work\AddMedia::class)->run($this->item, $file, $columnName);
    }

    /**
     * 添加附件组
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array<UploadedFile|string> $files
     * @param  string              $columnName
     * @return void
     */
    public function addMultipleMedia(array $files, string $columnName): void
    {
        foreach ($files as $file) {
            $this->addMedia($file, $columnName);
        }
    }

    /**
     * 检查是否存在Media关系
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return boolean
     */
    protected function hasMedia(): bool
    {
        return app(Work\HasMedia::class)->run($this->model);
    }

    /**
     * 获取Media Key
     */
    protected function getMediaColumn(): ?string
    {
        return defined($this->className . '::MEDIA_FILE') ? $this->className::MEDIA_FILE : null;
    }


    /**
     * 条件更新
     *
     * @param array $where 条件
     * @param array $data 更新参数
     * @return bool
     */
    public function updateV2(array $where, array $data)
    {
        DB::beginTransaction();
        try {
            $primaryKeyList = $this->model->lockForUpdate()->where($where)->select($this->primaryKey)->get()->pluck($this->primaryKey)->all();
            if ($primaryKeyList) {
                foreach ($primaryKeyList as $primaryKey) {
                    if ($item = $this->model->find($primaryKey)) {
                        $item->update($data);
                    }
                }
            }
            $result = true;
        } catch (\Exception $e) {
            $result = false;
            DB::rollBack();
        }
        DB::commit();
        if ($result) {
            $this->clearCacheData();
        }
        return $result;
    }

    /**
     * 删除单条数据
     *
     * @param string|int $id
     * @return boolean
     */
    public function delete(string|int $id)
    {
        $this->item = $this->model->where($this->primaryKey, $id)->first();
        if (!$this->item) {
            throw new SimpleException(trans('simplecms:delete_failed'));
        }
        $result = $this->item->delete();
        if ($result) {
            $this->clearCacheData();
        }
        return $result;
    }

    /**
     * 清空数据
     *
     * @return void
     */
    public function clean()
    {
        $this->model->truncate();
    }

    /**
     * 删除多条数据
     *
     * @param  array   $ids
     * @return boolean
     */
    public function batch_delete(array $ids)
    {
        $result = $this->model->destroy($ids);
        if ($result) {
            $this->clearCacheData();
        }
        return $result;
    }

    /**
     * 根据主键查找数据
     *
     * @param string|integer $id
     * @return Model|null
     */
    public function findById(string|int $id)
    {
        $where = [
            $this->primaryKey => $id
        ];
        return $this->find($where);
    }

    /**
     * 根据条件获取单条数据
     *
     * @param callable|null|array $where DB query
     * @return Model|null
     */
    public function find(callable|null|array $where = null)
    {
        if (!$where && $this->query) {
            $where = $this->query;
        }
        $builder = $this->model->where($where);
        if ($this->with) {
            $builder = $builder->with($this->with);
        }
        $builder = (new Work\MakeSelect)->run($this->model, $builder, $this->select);
        return $this->getCacheData([$builder->toRawSql()], fn() => $this->model->where($where)->first());
    }

    /**
     * 更新单个字段值
     *
     * @param string|int|array|callable $id 非数组时请求主键
     * @param string $field 键名
     * @param null|string|float|array<string|float,mixed> $value 键值
     * @return bool
     */
    public function setValue(string|int|array|callable $id, string $field, string|float|array $value = null)
    {
        if (!is_numeric($id) && !is_string($id)) {
            return $this->model->where($id)->update(["{$field}" => $value]);
        }
        $result = $this->model->where('id', $id)->update(["{$field}" => $value]);
        if ($result) {
            $this->clearCacheData();
        }
        return $result;
    }


    /**
     * 批量设置保存
     * 
     * @param string $field 键名
     * @param array<string|int,string|int> $data 请求保存项
     * @return boolean
     */
    public function quick(string $field, array $data)
    {
        $primaryKey = $this->model->getKeyName();
        $keys = [];
        $where = [];
        foreach ($data as $key => $value) {
            $keys[] = "'" . $key . "'";
            $where[] = "WHEN '$key' THEN '$value'";
        }
        $sql = "UPDATE `" . $this->model->getTable() . "` SET `$field` = CASE `$primaryKey` " . implode(" ", $where) . " ELSE $field END WHERE `$primaryKey` IN (" . implode(",", $keys) . ")";
        $result = DB::update($sql);
        if ($result) {
            $this->clearCacheData();
        }
        return $result;
    }
}
