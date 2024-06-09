<?php
namespace SimpleCMS\Framework\Packages\Menu;

/**
 * 菜单
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class MenuClass
{

    /**
     * 菜单名
     *
     * @var string|null
     */
    public ?string $name;

    /**
     * 路由
     *
     * @var array<string,string>
     */
    public ?array $url;

    /**
     * 是否显示
     *
     * @var boolean
     */
    public bool $is_show;

    /**
     * 是否当前页
     *
     * @var boolean
     */
    public bool $current;

    /**
     * 排序值
     *
     * @var int
     */
    public int $sort_order;

    /**
     * 同级
     *
     * @var array<self>|null
     */
    public array|null $siblings;

    /**
     * 下级
     *
     * @var array<self>|null
     */
    public array|null $children;

    /**
     * 下级
     *
     * @var self|null
     */
    public self|null $child;

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'url' => $this->url,
            'is_show' => $this->is_show,
            'current' => $this->current,
            'sort_order' => $this->sort_order,
            'siblings' => $this->siblings,
            'children' => $this->children,
            'child' => $this->child,
        ];
    }

    public function toString(): string
    {
        return json_encode($this->toArray());
    }
}