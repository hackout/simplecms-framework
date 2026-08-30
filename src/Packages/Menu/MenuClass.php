<?php
namespace SimpleCMS\Framework\Packages\Menu;

/**
 * 菜单
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class MenuClass implements \JsonSerializable
{

    /**
     * 菜单名
     *
     * @var string|null
     */
    public string|null $name = null;

    /**
     * 图标
     *
     * @var string|null
     */
    public string|null $icon = null;
    /**
     * 路由
     *
     * @var array<string,string>
     */
    public array|null $url = null;

    /**
     * 是否显示
     *
     * @var boolean
     */
    public bool $is_show = false;

    /**
     * 是否当前页
     *
     * @var boolean
     */
    public bool $current = false;

    /**
     * 排序值
     *
     * @var int
     */
    public int $sort_order = 0;

    /**
     * 同级
     *
     * @var array<MenuClass>|null
     */
    public array|null $siblings = null;

    /**
     * 下级
     *
     * @var array<MenuClass>|null
     */
    public array|null $children = null;

    /**
     * 下级
     *
     * @var MenuClass|null
     */
    public MenuClass|null $child = null;

    public function toArray(): array
    {
        $data = [
            'is_show' => $this->is_show,
            'current' => $this->current,
            'sort_order' => $this->sort_order,
            'url' => $this->url,
            'icon' => $this->icon,
            'name' => $this->name,
            'child' => $this->child ? $this->child->toArray() : null,
            'siblings' => json_decode(json_encode($this->siblings), true),
            'children' => json_decode(json_encode($this->children), true)
        ];

        return $data;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }
}