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

}