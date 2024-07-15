<?php

namespace SimpleCMS\Framework\Services;

use function is_array;
use function array_pad;
use function is_string;
use function is_numeric;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use SimpleCMS\Framework\Traits\ServiceMacroable;
use SimpleCMS\Framework\Exceptions\SimpleException;

/**
 * SimpleService for Based service
 */

class SimpleService extends CacheService implements CacheInterface
{
    use ServiceMacroable;

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
     * 获取所有参数
     *
     * @param array<string> $fieldList 显示选项
     *
     * @return Collection|null
     */
    public function getAll(array $fieldList = [])
    {
        $builder = $this->builder();

        $builder = (new Work\MakeSelect)->run($this->model, $builder, $this->select);
        $result = $this->getCacheData([$builder->toRawSql(), 'all'], fn() => $builder->get());
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
                $mediaColumn = $this->getMediaColumn() ?? head(array_keys($multipleFiles));
                $this->addMultipleMedia(head($multipleFiles), $mediaColumn);
            } else {
                foreach ($multipleFiles as $field => $file) {
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
     * DB请求
     * 
     * @param ?string $tableName
     * @return \Illuminate\Database\Query\Builder
     */
    public function db(string $tableName = null): \Illuminate\Database\Query\Builder
    {
        return DB::table($tableName ? $tableName : $this->model->getTable());
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
        $builder = $this->model;
        if ($this->query) {
            $builder = $builder->where($this->query);
        }
        if ($where) {
            $builder = $builder->where($where);
        }
        if ($this->with) {
            $builder = $builder->with($this->with);
        }
        $builder = (new Work\MakeSelect)->run($this->model, $builder, $this->select);
        return $this->getCacheData([$builder->toRawSql(), 'first', $where], fn() => $builder->first());
    }

    /**
     * Retrieve the "count" result of the query.
     *
     * @param  \Illuminate\Contracts\Database\Query\Expression|string  $columns
     * @return int
     */
    public function count($columns = '*'): int
    {
        $builder = $this->model;
        if ($this->query) {
            $builder = $builder->where($this->query);
        }
        if ($this->with) {
            $builder = $builder->with($this->with);
        }
        return $this->getCacheData([$builder->toRawSql(), 'count', $columns], fn() => $builder->count($columns));
    }

    /**
     * Retrieve the minimum value of a given column.
     *
     * @param  \Illuminate\Contracts\Database\Query\Expression|string  $column
     * @return mixed
     */
    public function min($column)
    {
        $builder = $this->model;
        if ($this->query) {
            $builder = $builder->where($this->query);
        }
        if ($this->with) {
            $builder = $builder->with($this->with);
        }
        return $this->getCacheData([$builder->toRawSql(), 'min', $column], fn() => $builder->min($column));
    }

    /**
     * Retrieve the maximum value of a given column.
     *
     * @param  \Illuminate\Contracts\Database\Query\Expression|string  $column
     * @return mixed
     */
    public function max($column)
    {
        $builder = $this->model;
        if ($this->query) {
            $builder = $builder->where($this->query);
        }
        if ($this->with) {
            $builder = $builder->with($this->with);
        }
        return $this->getCacheData([$builder->toRawSql(), 'max', $column], fn() => $builder->max($column));
    }

    /**
     * Retrieve the sum of the values of a given column.
     *
     * @param  \Illuminate\Contracts\Database\Query\Expression|string  $column
     * @return mixed
     */
    public function sum($column)
    {
        $builder = $this->model;
        if ($this->query) {
            $builder = $builder->where($this->query);
        }
        if ($this->with) {
            $builder = $builder->with($this->with);
        }
        return $this->getCacheData([$builder->toRawSql(), 'sum', $column], fn() => $builder->sum($column) ?: 0);

    }

    /**
     * Retrieve the average of the values of a given column.
     *
     * @param  \Illuminate\Contracts\Database\Query\Expression|string  $column
     * @return mixed
     */
    public function avg($column)
    {
        $builder = $this->model;
        if ($this->query) {
            $builder = $builder->where($this->query);
        }
        if ($this->with) {
            $builder = $builder->with($this->with);
        }
        return $this->getCacheData([$builder->toRawSql(), 'avg', $column], fn() => $builder->avg($column));
    }

    /**
     * Alias for the "avg" method.
     *
     * @param  \Illuminate\Contracts\Database\Query\Expression|string  $column
     * @return mixed
     */
    public function average($column)
    {
        return $this->avg($column);
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
