<?php
namespace SimpleCMS\Framework\Services\Traits;

use SimpleCMS\Framework\Exceptions\SimpleException;

/**
 * 移除数据处理类
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 * @use \SimpleCMS\Framework\Services\BaseService
 * @abstract \SimpleCMS\Framework\Services\BaseService
 * @use \SimpleCMS\Framework\Services\SimpleService
 * @abstract \SimpleCMS\Framework\Services\SimpleService
 */
trait RemoveServiceTrait
{

    /**
     * 删除单条数据
     *
     * @param string|int $id
     * @return boolean
     */
    public function delete(string|int $id)
    {
        $this->/** @scrutinizer ignore-call */setItem($this->/** @scrutinizer ignore-call */findById($id));
        if (!$item = $this->/** @scrutinizer ignore-call */getItem()) {
            throw new SimpleException(trans('simplecms:delete_failed'));
        }
        if ($result = $item->delete()) {
            $this->/** @scrutinizer ignore-call */clearCache();
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
        optional($this->/** @scrutinizer ignore-call */getModel())->truncate();
        $this->/** @scrutinizer ignore-call */clearCache();
    }

    /**
     * 删除多条数据
     *
     * @param  array<int,string|int>   $ids
     * @return boolean
     */
    public function batch_delete(array $ids)
    {
        $result = optional($this->/** @scrutinizer ignore-call */getModel())->destroy($ids);
        if ($result) {
            $this->/** @scrutinizer ignore-call */clearCache();
        }
        return (bool) $result;
    }

}