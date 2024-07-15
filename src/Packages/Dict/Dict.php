<?php
namespace SimpleCMS\Framework\Packages\Dict;

use SimpleCMS\Framework\Models\DictItem;
use Illuminate\Database\Eloquent\Collection;
use SimpleCMS\Framework\Models\Dict as DictModel;

/**
 * 字典操作类
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class Dict
{
    /**
     * 获取所有列表
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return Collection
     */
    public function getList(): Collection
    {
        return DictModel::select(['name', 'code'])->get()->sortBy([['sort_order', 'desc']]);
    }

    /**
     * 获取字典
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string         $code
     * @return DictModel|null
     */
    public function getDict(string $code): ?DictModel
    {
        return DictModel::where('code', $code)->first();
    }

    /**
     * 获取字典列表
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string     $code
     * @return Collection|\Illuminate\Support\Collection
     */
    public function getOptionsByCode(string $code): Collection|\Illuminate\Support\Collection
    {
        $dict = DictModel::where('code', $code)->first();
        if (!$dict)
            return collect();
        return $dict->items->map(fn(DictItem $dictItem) => [
            'name' => $dictItem->name,
            'value' => $dictItem->value
        ]);
    }

    /**
     * 键值获取键名
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string      $code
     * @param  integer     $value
     * @return string|null
     */
    public function getNameByValue(string $code, int $value): ?string
    {
        $dict = DictModel::where('code', $code)->first();
        if (!$dict)
            return null;
        return optional($dict->items->where('value', $value)->first())->name;
    }

    /**
     * 键名获取键值
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string      $code
     * @param  string      $name
     * @return string|null
     */
    public function getValueByName(string $code, string $name): ?string
    {
        $dict = DictModel::where('code', $code)->first();
        if (!$dict)
            return null;
        return optional($dict->items->where('name', $name)->first())->value;
    }

}