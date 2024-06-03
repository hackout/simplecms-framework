<?php
namespace SimpleCMS\Framework\Packages\Dict;

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
        return DictModel::select(['name', 'code'])->orderBy('sort_order')->get();
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
     * 添加字典
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string $name 名称
     * @param  string $code 标识
     * @param  array<array<string,string|int>>  $items 字典项
     * @return void
     */
    public function addDict(string $name, string $code, array $items): void
    {
        $sql = [
            'name' => $name,
            'code' => $code,
        ];
        if ($item = DictModel::create($sql)) {
            $item->items()->createMany($items);
        }
    }

    /**
     * 获取字典列表
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string     $code
     * @return Collection
     */
    public function getOptionsByCode(string $code): Collection
    {
        $dict = DictModel::where('code', $code)->first();
        if (!$dict)
            return collect([]);
        return $dict->items->only([
            'name',
            'value'
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