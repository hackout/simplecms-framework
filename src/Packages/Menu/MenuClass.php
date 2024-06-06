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
     * 子目录
     *
     * @var array<self>|self|null
     */
    public array|self|null $children;
}