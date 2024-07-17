<?php
namespace SimpleCMS\Framework\Services\Traits;

use SimpleCMS\Framework\Services\Work\ConvertData;
use function is_array;
use function is_callable;
use Illuminate\Support\Facades\DB;
use SimpleCMS\Framework\Exceptions\SimpleException;

/**
 * 更新数据处理类
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 * @use \SimpleCMS\Framework\Services\SimpleService
 * @abstract \SimpleCMS\Framework\Services\SimpleService
 */
trait UpdateServiceTrait
{

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

        list($sql, $files, $multipleFiles) = ConvertData::run($this->getModel(), $data, $mediaFields);
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
            error_log($e->getMessage());
        }

        if ($result) {
            $this->clearCache();
        }

        return $result;
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
        return (bool) $result;
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