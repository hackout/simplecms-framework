<?php

namespace SimpleCMS\Framework\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Database\Eloquent\Collection;
use SimpleCMS\Framework\Contracts\CacheInterface;
use SimpleCMS\Framework\Contracts\BuilderInterface;
use Illuminate\Support\Collection as BaseCollection;
use SimpleCMS\Framework\Contracts\RetrieveInterface;

/**
 * SimpleService for Based service
 */

class SimpleService extends BaseService implements CacheInterface, BuilderInterface, RetrieveInterface
{
    use Macroable,
        Traits\CacheServiceTrait,
        Traits\BuilderServiceTrait,
        Traits\RetrieveServiceTrait,
        Traits\MediaServiceTrait,
        Traits\RemoveServiceTrait,
        Traits\UpdateServiceTrait;

    protected string $name = 'simple.service';

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
        list($sql, $files, $multipleFiles,$mediaFields) = Work\ConvertData::run($this->getModel(), $data, $mediaFields);

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

}
