<?php
namespace SimpleCMS\Framework\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use SimpleCMS\Framework\Exceptions\SimpleException;

use function is_numeric;
use function is_string;
use function is_array;
use function array_slice;
use function array_pad;

/**
 * 基础服务类
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 *
 * @property string $className 模型路径
 * @property string|null $primaryKey 模型主键
 * @property string|null $orderKey 排序主键 (默认$primaryKey)
 * @property string<'DESC','ASC'> $orderType 排序 (默认DESC)
 * 
 * @property Model|null $item 单条数据,仅在create或update后存在
 * 
 * @method self setModel(string $className)
 * @method self setQuery(callable|null|array $query)
 * @method self listQuery(array $data = null, array $keys, array $sqlList = [])
 * @method self setWith(callable|null|array $with)
 * @method self setHas(null|array $has)
 * @method self setGroup(array|string|null $group)
 * @method self setSelect(callable|null|array $select)
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
 * @method array getRelationList(Model $item)
 * 
 */
class SimpleService
{

    public ?string $className = null;

    protected $query;

    protected $with;

    protected $has;

    protected $group;

    protected $select;

    protected $cacheName;

    public Model|HasMany|HasManyThrough|BelongsToMany|MorphMany $model;

    public string $primaryKey;

    public string $orderKey;

    public string $orderType = 'DESC';

    public null|Model $item = null;

    public function __construct()
    {
        if ($this->className) {
            $this->model = new $this->className;
            $this->primaryKey = $this->model->getKeyName();
            $this->orderKey = $this->model->timestamps ? 'created_at' : $this->primaryKey;
            $this->cacheName = $this->model->getTable();
        }
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
                $action = is_array($value) && array_key_exists(0, $value) ? $value[0] : $value;
                $fields = is_array($value) && array_key_exists(1, $value) ? $value[1] : $key;
                if ($action == 'search' && array_key_exists($key, $data) && trim($data[$key])) {
                    $keyword = trim($data[$key]);
                    $sql[] = [
                        function ($query) use ($keyword, $fields) {
                            if (is_array($fields)) {
                                foreach ($fields as $i => $field) {
                                    if ($i) {
                                        $query->orWhere($field, 'LIKE', "%{$keyword}%");
                                    } else {
                                        $query->where($field, 'LIKE', "%{$keyword}%");
                                    }
                                }
                            } else {
                                $query->where($fields, 'LIKE', "%{$keyword}%");
                            }
                        }
                    ];
                }
                if ($action == 'datetime_range' && array_key_exists($key, $data) && $data[$key]) {
                    $dateArray = is_array($data[$key]) ? $data[$key] : [$data[$key]];
                    $datetime = array_slice(array_pad($dateArray, 2, null), 0, 2);
                    if (is_array($fields)) {
                        throw new SimpleException(trans('simplecms::parameter_valid'));
                    }
                    if ($datetime[0]) {
                        $sql[] = [$fields, '>=', Carbon::parse($datetime[0])];
                    }
                    if ($datetime[1]) {
                        $sql[] = [$fields, '<', Carbon::parse($datetime[1])];
                    }
                }
                if ($action == 'range' && array_key_exists($key, $data) && $data[$key]) {
                    $dateArray = is_array($data[$key]) ? $data[$key] : [$data[$key]];
                    $datetime = array_slice(array_pad($dateArray, 2, null), 0, 2);
                    if (is_array($fields)) {
                        throw new SimpleException(trans('simplecms::parameter_valid'));
                    }
                    if ($datetime[0]) {
                        $sql[] = [$fields, '>=', $datetime[0]];
                    }
                    if ($datetime[1]) {
                        $sql[] = [$fields, '<', $datetime[1]];
                    }
                }
                if ($action == 'datetime' && array_key_exists($key, $data) && $data[$key]) {
                    $datetime = Carbon::parse($data[$key]);
                    if (is_array($fields)) {
                        throw new SimpleException(trans('simplecms::parameter_valid'));
                    }
                    $sql[] = [$fields, '>=', $datetime];
                }
                if ($action == 'date' && array_key_exists($key, $data) && $data[$key]) {
                    $datetime = Carbon::parse($data[$key]);
                    if (is_array($fields)) {
                        throw new SimpleException(trans('simplecms::parameter_valid'));
                    }
                    $sql[] = [$fields, 'Date', $datetime->toDateString()];
                }
                if ($action == 'year' && array_key_exists($key, $data) && $data[$key]) {
                    $datetime = Carbon::parse($data[$key]);
                    if (is_array($fields)) {
                        throw new SimpleException(trans('simplecms::parameter_valid'));
                    }
                    $sql[] = [$fields, 'Year', $datetime->year];
                }
                if ($action == 'month' && array_key_exists($key, $data) && $data[$key]) {
                    $datetime = Carbon::parse($data[$key]);
                    if (is_array($fields)) {
                        throw new SimpleException(trans('simplecms::parameter_valid'));
                    }
                    $sql[] = [$fields, 'Month', $datetime->month];
                }
                if ($action == 'day' && array_key_exists($key, $data) && $data[$key]) {
                    $datetime = Carbon::parse($data[$key]);
                    if (is_array($fields)) {
                        throw new SimpleException(trans('simplecms::parameter_valid'));
                    }
                    $sql[] = [$fields, 'Day', $datetime->day];
                }
                if ($action == 'column' && array_key_exists($key, $data) && $data[$key]) {
                    if (is_array($fields)) {
                        throw new SimpleException(trans('simplecms::parameter_valid'));
                    }
                    $sql[] = [$fields, 'Column', $data[$key]];
                }
                if ($action == 'in' && array_key_exists($key, $data) && $data[$key]) {
                    if (is_array($fields)) {
                        throw new SimpleException(trans('simplecms::parameter_valid'));
                    }
                    $arr = is_array($data[$key]) ? $data[$key] : [$data[$key]];
                    $sql[] = [
                        function ($query) use ($fields, $arr) {
                            $query->whereIn($fields, $arr);
                        }
                    ];
                }
                if ($action == 'eq' && array_key_exists($key, $data) && $data[$key] !== null) {
                    if (is_array($fields)) {
                        throw new SimpleException(trans('simplecms::parameter_valid'));
                    }
                    $sql[] = [$fields, '=', $data[$key]];
                }
                if ($action == 'like' && array_key_exists($key, $data) && $data[$key] !== null) {
                    if (is_array($fields)) {
                        throw new SimpleException(trans('simplecms::parameter_valid'));
                    }
                    $sql[] = [$fields, 'LIKE', $data[$key]];
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
                list ($key, $val) = array_pad(explode(' as ', strtolower($value)), 2, null);
                list ($relation, $key1) = array_pad(explode('.', strtolower($key)), 2, null);
                $key1 = $key1 ?: $relation;
                $val = $val ?: $key1;
                if ($relation === $key) {
                    if (strpos($relation, ':') === false) {
                        $newItem->put($val, $item->{$key});
                    } else {
                        list ($relation, $relationColumnString) = explode(':', $relation);
                        list ($val, $valColumnString) = array_pad(explode(':', $val), 2, null);
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
                        list ($relation, $relationColumnString) = explode(':', $relation);
                        list ($val, $valColumnString) = array_pad(explode(':', $val), 2, null);
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

    public function getBuilder(): Builder
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
        if ($this->select) {
            $builder = $builder->select($this->select);
        }
        if ($this->has) {
            foreach ($this->has as $key => $value) {
                $builder = $builder->whereHas($key, function ($q) use ($value) {
                    $q->where($value);
                });
            }
        }
        if ($prop && $order) {
            $builder = $builder->orderBy($prop, str_replace('ending', '', $order));
        } else {
            if ($timestamps) {
                $builder = $builder->orderBy($this->orderKey, $this->orderType);
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
        $fields = request()->get('fields', '*');
        $builder = $this->builder($prop, $order);
        $data = $this->getCacheData([$builder->toRawSql(), $limit, explode(',', $fields), 'page', $page], fn() => $builder->paginate($limit, explode(',', $fields), 'page', $page));
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
     * @param array<string,mixed> $data 数据参数
     * @return bool
     */
    public function create(array $data)
    {
        $this->item = $this->newModel();
        $this->item->fill($data);
        $result = $this->item->save();

        if ($result) {
            $this->clearCacheData();
        }

        return $result;
    }


    /**
     * 更新数据
     *
     * @param string|int $id 主键
     * @param array $data 更新参数
     * @return bool
     */
    public function update(string|int $id, array $data)
    {
        $this->item = $this->model->where($this->primaryKey, $id)->first();

        if (!$this->item) {
            throw new SimpleException(trans('simplecms:not_exists'));
        }

        $this->item->fill($data);
        $result = $this->item->save();

        if ($result) {
            $this->clearCacheData();
        }

        return $result;
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
     * 检查关联数据返回下级列表
     *
     * @param  Model $item
     * @return array
     */
    public function getRelationList(Model $item)
    {
        $model = new \ReflectionClass($this->className);
        $document = $model->getDocComment();
        $array = [];
        if ($document) {
            $strings = explode("\n", $document);
            foreach ($strings as $string) {
                $string = trim(str_replace('*', '', $string));
                $arr = explode(" ", $string);
                if (count($arr) > 1 && $arr[0] == '@property-read') {
                    if (strpos($string, 'Many') !== false || strpos($string, 'One') !== false) {
                        $array[] = substr($arr[2], 1);
                    }
                }
            }
        }
        $result = [];
        if ($array) {
            foreach ($array as $key) {
                if ($item->{$key}) {
                    if ($item->{$key} instanceof Collection) {
                        if ($count = $item->{$key}->count()) {
                            $result[$key] = $count;
                        }
                    } else {
                        $result[$key] = 1;
                    }
                }
            }
        }
        return $result;
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
            "{$this->primaryKey}" => $id
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