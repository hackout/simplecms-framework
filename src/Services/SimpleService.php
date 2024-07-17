<?php

namespace SimpleCMS\Framework\Services;

use function is_array;
use function is_callable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Database\Eloquent\Collection;
use SimpleCMS\Framework\Contracts\CacheInterface;
use SimpleCMS\Framework\Contracts\BuilderInterface;
use SimpleCMS\Framework\Exceptions\SimpleException;
use Illuminate\Support\Collection as BaseCollection;
use SimpleCMS\Framework\Contracts\RetrieveInterface;

/**
 * SimpleService for Based service
 */

class SimpleService extends BaseService implements CacheInterface, BuilderInterface, RetrieveInterface
{
    use Macroable, CacheServiceTrait, BuilderServiceTrait, RetrieveServiceTrait;

    protected string $name = 'simple.service';
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

    private function convertCondition(array $data, array $conditions): BaseCollection
    {
        $result = new BaseCollection();
        foreach ($conditions as $key => $value) {
            if (isset($data[$key])) {
                $result->put($key, $value);
            }
        }
        return $result;
    }

    /**
     * 进阶搜索
     * 
     * @param  ?array $data 查询条件
     * @param  array<string,string|array<string>>      $conditions 查询动作方式
     * @param  ?array      $otherConditions 附加条件
     * @return self
     */
    public function listQuery(array|null $data = null, array $conditions, array $otherConditions = [])
    {
        if ($data) {
            $sql = $otherConditions ?: [];
            $newConditions = $this->convertCondition($data, $conditions);
            $newConditions->each(function ($value, $key) use ($data, &$sql) {
                list($queryModel, $fields, $extra) = Work\ConvertQueryParam::run($value, $key);
                if ($_sql = $queryModel::builder($data[$key], $fields, $extra)) {
                    $sql[] = $_sql;
                }
            });
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
        $builder = $this->getBuilder();
        $result = $this->getCacheData([$builder->toRawSql(), 'all'], fn() => $builder->get());
        return Work\FilterData::run($result, $fieldList);
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
        $builder = $this->getBuilder($prop, $order);
        $data = $this->getCacheData([$builder->toRawSql(), $limit, 'page', $page], fn() => $builder->paginate($limit, ['*'], 'page', $page));
        $items = Work\FilterData::run(collect($data->items()), $fieldList);
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
        list($sql, $files, $multipleFiles) = Work\ConvertData::run($this->getModel(), $data, $mediaFields);

        $item = $this->newModel();
        $item->fill($sql);
        $result = $item->save();
        if ($result) {
            $this->setItem($item);
            if ($this->hasMedia()) {
                $this->updateMedia($files, $multipleFiles, $mediaFields);
            }
            $this->clearCache();
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
        $this->setItem($this->findById($id));
        if (!$item = $this->getItem()) {
            throw new SimpleException(trans('simplecms:not_exists'));
        }

        list($sql, $files, $multipleFiles) = Work\ConvertData::run($this->getModel(), $data, $mediaFields);
        $item->fill($sql);
        $result = $item->save();

        if ($result) {
            if ($this->hasMedia()) {
                $this->updateMedia($files, $multipleFiles, $mediaFields);
            }
            $this->clearCache();
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
        if (!empty($files)) {
            if (empty($mediaFields)) {
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
        if (!empty($multipleFiles)) {

            if (empty($mediaFields)) {
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
        Work\AddMedia::run($this->getItem(), $file, $columnName);
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
        return Work\HasMedia::run($this->getModel());
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
        $model = $this->getModel();
        $result = false;
        if (!$model) {
            return $result;
        }
        DB::beginTransaction();
        try {
            $primaryKeyList = $model->lockForUpdate()->where($where)->pluck($this->primaryKey)->all();
            if (!empty($primaryKeyList)) {
                $model->whereIn($this->primaryKey, $primaryKeyList)->update($data);
                $result = true;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger($e->getMessage());
        }

        if ($result) {
            $this->clearCache();
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
        $this->setItem($this->findById($id));
        if (!$item = $this->getItem()) {
            throw new SimpleException(trans('simplecms:delete_failed'));
        }
        if ($result = $item->delete()) {
            $this->clearCache();
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
        optional($this->getModel())->truncate();
        $this->clearCache();
    }

    /**
     * 删除多条数据
     *
     * @param  array<int,string|int>   $ids
     * @return boolean
     */
    public function batch_delete(array $ids)
    {
        $result = optional($this->getModel())->destroy($ids);
        if ($result) {
            $this->clearCache();
        }
        return (bool) $result;
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
            $this->getPrimaryKey() => $id
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
        if ($where !== null) {
            $this->setQuery($where);
        }
        $builder = $this->getBuilder();
        return $this->getCacheData([$builder->toRawSql(), 'first', $where], fn() => $builder->first());
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
        $model = $this->getModel();
        $result = false;
        if (!$model) {
            return $result;
        }
        $type = 'string';
        if (is_array($id)) {
            $type = 'array';
        } elseif (is_callable($id)) {
            $type = 'callable';
        }
        if (in_array($type, ['array', 'callable'])) {
            $result = $model->where($id)->update(["{$field}" => $value]);
        } else {
            $result = $model->where('id', $id)->update(["{$field}" => $value]);
        }

        if ($result) {
            $this->clearCache();
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
        $primaryKey = $this->getPrimaryKey();
        $keys = [];
        $where = [];
        foreach ($data as $key => $value) {
            $keys[] = "'" . $key . "'";
            $where[] = "WHEN '$key' THEN '$value'";
        }
        $sql = "UPDATE `" . $this->getTableName() . "` SET `$field` = CASE `$primaryKey` " . implode(" ", $where) . " ELSE $field END WHERE `$primaryKey` IN (" . implode(",", $keys) . ")";
        $result = DB::update($sql);
        if ($result) {
            $this->clearCache();
        }
        return $result;
    }
}
